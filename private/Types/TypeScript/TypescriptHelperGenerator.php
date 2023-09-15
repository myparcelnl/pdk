<?php
/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\TypeScript;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Console\Types\Shared\AbstractHelperGenerator;
use MyParcelNL\Pdk\Console\Types\Shared\Collection\ClassDefinitionCollection;
use MyParcelNL\Pdk\Context\Context;
use ReflectionClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TypescriptHelperGenerator extends AbstractHelperGenerator
{
    final public const  UNDEFINED     = 'undefined';
    final public const  SPACES_AMOUNT = 2;
    final public const  UNKNOWN       = 'unknown';
    private const       FALLBACK_TYPE = 'Record<string, ' . self::UNKNOWN . '>';

    private readonly TsTypeParser $parser;

    public function __construct(
        InputInterface            $input,
        OutputInterface           $output,
        ClassDefinitionCollection $definitions,
        string                    $baseDir
    ) {
        parent::__construct($input, $output, $definitions, $baseDir);
        $this->parser = new TsTypeParser();
    }

    /**
     * @param         $item
     */
    public function createPropertiesString($item, string $spaces): string
    {
        $string = '';

        // filter properties, removing items without the name key
        $properties = Arr::where($item['properties'] ?? [], static fn($value, $name) => (bool) $name);

        if (count($properties)) {
            if ($item['extends']) {
                $string .= ' & ';
            }

            $string .= '{';

            foreach ($properties as $name => $types) {
                $hasUndefined = in_array(self::UNDEFINED, $types, true);

                if ($hasUndefined && count($types)) {
                    $types = array_filter($types, static fn($item) => self::UNDEFINED !== $item);
                }

                $fallbackType = $hasUndefined ? [self::UNDEFINED] : [self::UNKNOWN];

                $string .= sprintf(
                    '%s%s%s: %s;',
                    $spaces,
                    $name,
                    $hasUndefined ? '?' : '',
                    implode(' | ', $types ?: $fallbackType)
                );
            }

            return $string . (PHP_EOL . str_repeat(' ', self::SPACES_AMOUNT) . '}');
        }

        if (! $item['extends']) {
            $string .= self::FALLBACK_TYPE;
        }

        return $string;
    }

    /**
     * @param         $item
     */
    public function createTypeString($item, string $spaces): string
    {
        if ($item['extends'] === $this->parser->getType('\\' . Collection::class, true)) {
            return $item['properties']['items'][0] ?? 'unknown[]';
        }

        if ($item['extends'] === $this->parser->getType('\\' . Model::class, true)) {
            unset($item['extends']);
        }

        $properties = $this->createPropertiesString($item, PHP_EOL . $spaces);

        return ($item['extends'] ?? '') . $properties;
    }

    /**
     * @param  null|\ReflectionClass $ref
     */
    public function getPropertyTypesRecursive(
        ?ReflectionClass $ref,
        string           $baseProperty,
        array            $types = []
    ): array {
        $types ??= [];

        if ($ref && $ref->hasProperty($baseProperty)) {
            $property = $ref->getProperty($baseProperty);
            $type     = $property->getType();

            if ($type) {
                $tsType = $this->parser->getType($type->getName());

                if (! in_array($tsType, $types, true)) {
                    $types[] = $tsType;
                }
            }
        }

        return $types;
    }

    protected function gatherContent(): array
    {
        $items = [];

        foreach ($this->definitions->all() as $definition) {
            $parentRef = $definition->ref->getParentClass() ?: null;

            $parts = explode('\\', (string) $definition->ref->getName());
            $group = null;

            if (count($parts) > 3) {
                $group = $parts[2] ?? null;
            }

            $item = [
                'name'       => $this->parser->getType("\\{$definition->ref->getName()}"),
                'extends'    => $parentRef ? $this->parser->getType("\\{$parentRef->getName()}", true) : null,
                'group'      => $group,
                'properties' => [],
            ];

            foreach ($definition->properties->all() as $property) {
                if ('property' !== $property['param']) {
                    continue;
                }

                $item['properties'][$property['name']] = array_map(
                    fn(string $type) => $this->parser->getType($type, true),
                    $property['types']
                );
            }

            foreach ($definition->properties->all() as $property) {
                $baseProperty = $property->getName();
                $types        = $this->getPropertyTypesRecursive($definition->ref, $baseProperty);

                if (! $parentRef || count($types)) {
                    $item['properties'][$property->getName()] = $types;
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    protected function generate(): void
    {
        $handle  = $this->getHandle($this->getFilename());
        $spaces  = str_repeat(' ', self::SPACES_AMOUNT);
        $content = $this->gatherContent();

        usort($content, static function (array $itemA, array $itemB) {
            if ($itemA['group'] === $itemB['group']) {
                return $itemA['name'] <=> $itemB['name'];
            }

            return $itemA['group'] <=> $itemB['group'];
        });

        $lastGroup = null;

        $lines = [];

        foreach ($content as $index => $item) {
            $localSpaces = $spaces;

            if ($item['group']) {
                $localSpaces = str_repeat(' ', self::SPACES_AMOUNT);
            }

            if ($item['group'] && $lastGroup !== $item['group']) {
                $lines[]   = sprintf('export namespace %s {', $item['group']);
                $lastGroup = $item['group'];
            }

            $collectionType = $this->parser->getType('\\' . Collection::class);

            if ($item['name'] !== $collectionType) {
                $typeString = $this->createTypeString($item, $spaces . $localSpaces);

                $lines[] = sprintf(
                    '%sexport type %s = %s',
                    $localSpaces,
                    $item['name'],
                    (trim($typeString)) . ';'
                );
            }

            if ($item['group'] && ($content[$index + 1]['group'] !== $item['group'] || ! $content[$index + 1])) {
                $lines[] = '}' . PHP_EOL;
            }
        }

        $this->write(
            $handle,
            "// noinspection JSUnusedGlobalSymbols\n// @ts-nocheck\n/* eslint-disable */\n" . implode(PHP_EOL, $lines)
        );
        $this->close($handle, $this->getFilename());
    }

    /**
     * @return string[]
     */
    protected function getAllowedClasses(): array
    {
        return [
            Collection::class,
            Context::class,
            Model::class,
            Request::class,
        ];
    }

    private function getFilename(): string
    {
        return "$this->baseDir/types/typescript/myparcel-pdk.d.ts";
    }
}

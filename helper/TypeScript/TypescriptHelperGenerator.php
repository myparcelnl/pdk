<?php
/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\TypeScript;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Request\Request;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Helper\Shared\AbstractHelperGenerator;
use MyParcelNL\Pdk\Plugin\Context;
use ReflectionClass;
use ReflectionProperty;

class TypescriptHelperGenerator extends AbstractHelperGenerator
{
    public const  UNDEFINED     = 'undefined';
    public const  SPACES_AMOUNT = 2;
    public const  UNKNOWN       = 'unknown';
    private const FALLBACK_TYPE = 'Record<string, ' . self::UNKNOWN . '>';

    /**
     * @var \MyParcelNL\Pdk\Helper\TypeScript\TypeParser
     */
    private $parser;

    public function __construct()
    {
        $this->parser = new TypeParser();
    }

    /**
     * @param         $item
     * @param  string $spaces
     *
     * @return string
     */
    public function createPropertiesString($item, string $spaces): string
    {
        $string = '';

        // filter properties, removing items without the name key
        $properties = Arr::where($item['properties'] ?? [], static function ($value, $name) {
            return (bool) $name;
        });

        if (count($properties)) {
            if ($item['extends']) {
                $string .= ' & ';
            }

            $string .= '{';

            foreach ($properties as $name => $types) {
                $hasUndefined = in_array(self::UNDEFINED, $types, true);

                if ($hasUndefined && count($types)) {
                    $types = array_filter($types, static function ($item) {
                        return self::UNDEFINED !== $item;
                    });
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

            $string .= PHP_EOL . str_repeat(' ', self::SPACES_AMOUNT) . '}';

            return $string;
        }

        if (! $item['extends']) {
            $string .= self::FALLBACK_TYPE;
        }

        return $string;
    }

    /**
     * @param         $item
     * @param  string $spaces
     *
     * @return string
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
     * @param  string                $baseProperty
     * @param  array                 $types
     *
     * @return array
     */
    public function getPropertyTypesRecursive(
        ?ReflectionClass $ref,
        string           $baseProperty,
        array            $types = []
    ): array {
        $types = $types ?? [];

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

    /**
     * @return array
     */
    protected function gatherContent(): array
    {
        $items = [];

        foreach ($this->data as $data) {
            $ref       = $this->reflectionCache[$data['class']];
            $parentRef = $ref->getParentClass() ?: null;

            $parts = explode('\\', $ref->getName());
            $group = null;

            if (count($parts) > 3) {
                $group = $parts[2] ?? null;
            }

            $item = [
                'name'       => $this->parser->getType("\\{$ref->getName()}"),
                'extends'    => $parentRef ? $this->parser->getType("\\{$parentRef->getName()}", true) : null,
                'group'      => $group,
                'properties' => [],
            ];

            foreach ($data['properties'] as $property) {
                if ('property' !== $property['param']) {
                    continue;
                }

                $item['properties'][$property['name']] = array_map(function (string $type) {
                    return $this->parser->getType($type, true);
                }, $property['types']);
            }

            foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
                $baseProperty = $property->getName();
                $types        = $this->getPropertyTypesRecursive($ref, $baseProperty);

                if (! $parentRef || count($types)) {
                    $item['properties'][$property->getName()] = $types;
                }
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @return string
     */
    protected function getFileName(): string
    {
        return BASE_DIR . '/types/typescript/myparcel-pdk.d.ts';
    }

    /**
     * @return array|string[]
     */
    protected function getWhitelistClasses(): array
    {
        return [
            Collection::class,
            Context::class,
            Model::class,
            Request::class,
        ];
    }

    /**
     * @return void
     */
    protected function write(): void
    {
        fwrite(
            $this->getHandle(),
            "// noinspection JSUnusedGlobalSymbols\n// @ts-nocheck\n/* eslint-disable */\n\n"
        );

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

        fwrite($this->getHandle(), implode(PHP_EOL, $lines));
    }
}

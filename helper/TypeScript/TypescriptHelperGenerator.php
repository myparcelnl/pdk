<?php
/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\TypeScript;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Request\Request;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Frontend\Form\PlainElement;
use MyParcelNL\Pdk\Helper\Shared\AbstractHelperGenerator;
use MyParcelNL\Pdk\Plugin\Context;
use ReflectionClass;
use ReflectionProperty;

class TypescriptHelperGenerator extends AbstractHelperGenerator
{
    public const         UNDEFINED     = 'undefined';
    public const         SPACES_AMOUNT = 2;
    public const         CONVERT_NAMES = [
        'class'     => 'class1',
        'case'      => 'case1',
        'default'   => 'default1',
        'interface' => 'interface1',
    ];
    public const         UNKNOWN       = 'unknown';
    private const        FALLBACK_TYPE = 'Record<string, ' . self::UNKNOWN . '>';

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

            $string .= '{' . PHP_EOL;

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

            $string .= PHP_EOL . '}';

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
     * @param  string $localSpaces
     *
     * @return string
     */
    public function createTypeString($item, string $spaces, string $localSpaces): string
    {
        if ($item['extends'] === $this->parser->getType('\\' . Collection::class, true)) {
            return $item['properties']['items'][0] ?? 'unknown[]';
        }

        if ($item['extends'] === $this->parser->getType('\\' . Model::class, true)) {
            unset($item['extends']);
        }

        $properties = $this->createPropertiesString($item, PHP_EOL . $spaces . $localSpaces);

        return ($item['extends'] ?? '') . $properties;
    }

    //    /**
    //     * @param  \ReflectionClass $class
    //     * @param  array            $parents
    //     *
    //     * @return bool
    //     */
    //    protected function classAllowed(ReflectionClass $class, array $parents): bool
    //    {
    //        return ! $class->isInternal();
    //    }/**

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

            //            $types = array_reduce($comment, function (array $carry, array $item) {
            //                foreach ($item['types'] as $type) {
            //                    if (in_array($type, $carry, true)) {
            //                        continue;
            //                    }
            //
            //                    $carry[] = $this->getTsType($type);
            //                }
            //
            //                return $carry;
            //            }, $types);

            //            if (! count($types) && $ref->getParentClass()) {
            //                $typesFromParent = $this->getPropertyTypesRecursive(
            //                    $ref->getParentClass(),
            //                    $baseProperty,
            //                    $types
            //                );
            //
            //                foreach ($typesFromParent as $typeFromParent) {
            //                    if (in_array($typeFromParent, $types, true)) {
            //                        continue;
            //                    }
            //
            //                    $types[] = $typeFromParent;
            //                }
            //            }
        }

        return $types;
    }

    /**
     * @param $type
     *
     * @return string
     */
    public function getReflectionTypeName($type): string
    {
        return array_key_exists($type->getName(), self::CONVERT_NAMES)
            ? self::CONVERT_NAMES[$type->getName()]
            : $type->getName();
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
                //                if (($parentRef
                //                    && is_a(
                //                        \MyParcelNL\Sdk\src\Support\Collection::class,
                //                        $parentRef
                //                            ->getName()
                //                    ))
                //                ) {
                //                    continue;
                //                }

                $baseProperty = $property->getName();
                $types        = $this->getPropertyTypesRecursive($ref, $baseProperty);

                if (! $parentRef || count($types)) {
                    $item['properties'][$property->getName()] = $types;
                }

                //                $tsTypes = array_map(function ($type) {
                //                    return $this->getTsType($type);
                //                }, [$types]);
                //
                //                sort($tsTypes);
                //
                //                if (in_array('undefined', $tsTypes, true) && count($tsTypes) > 1) {
                //                    $tsTypes = Arr::where($tsTypes, static function (string $item) {
                //                        return 'undefined' !== $item;
                //                    });
                //
                //                    $baseProperty .= '?';
                //                }
                //
                //                $item['properties'][] = sprintf('%s%s: %s;', '  ', $baseProperty, implode(' | ', $tsTypes));
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
            PlainElement::class,
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

        foreach ($content as $index => $item) {
            $string      = '';
            $localSpaces = $spaces;

            if ($item['group']) {
                $localSpaces = str_repeat(' ', self::SPACES_AMOUNT * 2);
            }

            if ($item['group'] && $lastGroup !== $item['group']) {
                $string    .= sprintf("export namespace %s {\n", $item['group']);
                $lastGroup = $item['group'];
            }

            $collectionType = $this->parser->getType('\\' . Collection::class);

            if ($item['name'] !== $collectionType) {
                $typeString = $this->createTypeString($item, $spaces, $localSpaces);

                $string .= sprintf(
                    "%sexport type %s = %s\n",
                    $localSpaces,
                    $item['name'],
                    (trim($typeString)) . ';' . PHP_EOL
                );
            }

            if ($item['group'] && ($content[$index + 1]['group'] !== $item['group'] || ! $content[$index + 1])) {
                $string = trim($string) . PHP_EOL . '}' . PHP_EOL;
            }

            fwrite($this->getHandle(), $string);
        }
    }

    /**
     * @param  array $items
     *
     * @return void
     */
    private function filterItems(array $items)
    {
    }
}

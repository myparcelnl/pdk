<?php
/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\TypeScript;

use ArrayIterator;
use DateTime;
use DateTimeImmutable;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Helper\Shared\AbstractHelperGenerator;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;
use Reflector;

class TypescriptHelperGenerator extends AbstractHelperGenerator
{
    private const SPACES_AMOUNT = 2;
    private const CONVERT_TYPES = [
        ArrayIterator::class     => 'unknown[]',
        DateTime::class          => 'Record<string, unknown>',
        DateTimeImmutable::class => 'Record<string, unknown>',
        'array'                  => 'unknown[]',
        'bool'                   => 'boolean',
        'callable'               => '((...args: unknown[]) => unknown)',
        'closure'                => '((...args: unknown[]) => unknown)',
        'double'                 => 'number',
        'float'                  => 'number',
        'int'                    => 'number',
        'integer'                => 'number',
        'mixed'                  => 'unknown',
        'null'                   => 'undefined',
    ];
    private const CONVERT_NAMES = [
        'class'     => 'class1',
        'case'      => 'case1',
        'default'   => 'default1',
        'interface' => 'interface1',
    ];

    private $tsTypeCache = [];

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
     * @param  \Reflector $reflector
     * @param  string     $default
     *
     * @return string
     */
    public function getReflectorName(Reflector $reflector, string $default = 'unknown'): string
    {
        if (! method_exists($reflector, 'getType')) {
            return $default;
        }

        $type = $reflector->getType();

        if (! $type) {
            return $default;
        }

        return $this->getReflectionTypeName($type);
    }

    /**
     * @return string
     */
    protected function getFileName(): string
    {
        return BASE_DIR . '/types/myparcel-pdk.d.ts';
    }

    /**
     * @return array|string[]
     */
    protected function getWhitelistClasses(): array
    {
        return [];
    }

    /**
     * @return void
     */
    protected function write(): void
    {
        fwrite(
            $this->getFileHandle(),
            "// eslint-disable-next-line @typescript-eslint/ban-ts-comment\n// @ts-nocheck\n/* eslint-disable */\n// noinspection JSUnusedGlobalSymbols\n\nexport namespace MyParcelPdk {"
        );
        $spaces = str_repeat(' ', self::SPACES_AMOUNT);

        foreach ($this->data as $data) {
            /** @var \ReflectionClass $reflectionClass */
            $reflectionClass = $data['reflectionClass'];
            $properties      = $data['properties'];
            $parents         = $data['parents'];

            $isFacade = in_array(Facade::class, $parents, true);

            if ($isFacade || $reflectionClass->isInterface() || $reflectionClass->isTrait()) {
                continue;
            }

            $isCollection = in_array(Collection::class, $parents, true);
            $isModel      = in_array(Model::class, $parents, true);
            $type         = $isCollection ? 'type' : 'interface';

            sort($properties);

            $modelProperties = [];
            $data            = [];

            if (! $isModel) {
                foreach ($reflectionClass->getProperties(T_PUBLIC) as $property) {
                    $modelProperties[] = sprintf(
                        '%s%s: %s;',
                        $spaces,
                        $property->getName(),
                        $this->getReflectorName($property)
                    );
                }
            }

            foreach ($properties as $property) {
                if ($isCollection && 'items' !== $property['name']) {
                    continue;
                }

                $baseProperty = $property['name'];
                $tsTypes      = array_map(function ($string) {
                    return $this->getTsType($string);
                }, $property['types'] ?? []);

                sort($tsTypes);

                if (in_array('undefined', $tsTypes, true) && count($tsTypes) > 1) {
                    $tsTypes = Arr::where($tsTypes, static function (string $item) {
                        return 'undefined' !== $item;
                    });

                    $baseProperty .= '?';
                }

                if ('interface' === $type) {
                    $modelProperties[] = sprintf('%s%s: %s;', $spaces, $baseProperty, implode(' | ', $tsTypes));
                } else {
                    array_push(
                        $data,
                        ...array_map(static function ($type) {
                            return str_replace('[]', '', $type);
                        }, $tsTypes)
                    );
                }
            }

            if ('interface' === $type) {
                $value = sprintf("{\n%s%s\n%s}", $spaces, implode(PHP_EOL . $spaces, $modelProperties), $spaces);
            } else {
                $multiple = count($data) > 1;
                $value    = sprintf('= %s%s%s[];', $multiple ? '(' : '', implode(' | ', $data), $multiple ? ')' : '');
            }

            $extends = 'interface' === $type && $reflectionClass->getParentClass()
                ? sprintf(
                    ' extends %s',
                    $this->getClassName(
                        $reflectionClass->getParentClass()
                            ->getName()
                    )
                ) : '';

            fwrite(
                $this->getFileHandle(),
                sprintf(
                    "\n%sexport %s %s%s %s\n",
                    $spaces,
                    $type,
                    $this->getClassName($reflectionClass->getName()),
                    $extends,
                    $value
                )
            );
        }

        fwrite($this->getFileHandle(), "}\n");
    }

    /**
     * @param  string $class
     *
     * @return string
     */
    private function getClassName(string $class): string
    {
        $parts = explode('\\', ltrim($class, '\\'));

        if (count($parts) > 2) {
            $parts = array_slice($parts, 2);

            $kebab = Str::kebab(Arr::last($parts));

            if ($parts[1] === ucfirst(Arr::last(explode('-', $kebab)))) {
                unset($parts[1]);
            }
        }

        return implode('', $parts);
    }

    /**
     * @param  string $string
     *
     * @return string
     */
    private function getTsType(string $string): string
    {
        if (! isset($this->tsTypeCache[$string])) {
            $result = $string;
            $base   = $string;

            if (Str::startsWith($string, '\\')) {
                $base   = $this->getClassName($string);
                $result = $base;
            }

            if (array_key_exists($base, self::CONVERT_TYPES)) {
                $result = self::CONVERT_TYPES[$base];
            } elseif (array_key_exists(strtolower($base), self::CONVERT_TYPES)) {
                $result = self::CONVERT_TYPES[strtolower($base)];
            } elseif (preg_match('/array\{\s*(.+?)\s*,\s*(.+?)\s*}/', $string, $subTypes)) {
                $type1  = $this->getTsType($subTypes[1]);
                $type2  = $this->getTsType($subTypes[2]);
                $result = "Record<$type1, $type2>";
            } elseif (Str::startsWith($string, 'array<')) {
                preg_match('/<(.+)>/', $string, $matches);

                $types       = array_map([$this, 'getTsType'], explode(',', $matches[1]));
                $typesString = implode(',', $types);

                $result = "Record<$typesString>";
            }

            $this->tsTypeCache[$string] = $result;
        }

        return $this->tsTypeCache[$string];
    }
}

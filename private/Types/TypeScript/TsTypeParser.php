<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\TypeScript;

use ArrayIterator;
use DateTime;
use DateTimeImmutable;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Console\Types\Shared\Concern\UsesCache;
use MyParcelNL\Sdk\src\Support\Str;

class TsTypeParser
{
    use UsesCache;

    final public const  CONVERT_TYPES = [
        ArrayIterator::class     => 'unknown[]',
        DateTime::class          => self::TYPE_DATE,
        DateTimeImmutable::class => 'Readonly<' . self::TYPE_DATE . '>',
        'array'                  => 'unknown[]',
        'bool'                   => 'boolean',
        'callable'               => self::TYPE_FUNCTION,
        'closure'                => self::TYPE_FUNCTION,
        'double'                 => self::TYPE_NUMBER,
        'float'                  => self::TYPE_NUMBER,
        'int'                    => self::TYPE_NUMBER,
        'integer'                => self::TYPE_NUMBER,
        'mixed'                  => 'unknown',
        'null'                   => TypescriptHelperGenerator::UNDEFINED,
        'resource'               => 'unknown',
    ];
    private const       TYPE_NUMBER   = 'number';
    private const       TYPE_RECORD   = 'Record<string, unknown>';
    private const       TYPE_FUNCTION = '((...args: unknown[]) => unknown)';
    private const       TYPE_DATE     = '{ date: string, timezone_type: number, timezone: string }';
    private const NAMESPACE           = '\\MyParcelNL\\Pdk\\';

    public function getType(string $string, bool $asReference = false): string
    {
        return $this->cache(sprintf('ts_type_%s', "{$string}_$asReference"), function () use ($asReference, $string) {
            $string = trim($string);

            $result = $string;
            $base   = $string;

            if (Str::startsWith($string, '\\')) {
                $result = $this->fromNamespacedClass($string, $asReference);
            } elseif (array_key_exists($base, self::CONVERT_TYPES)) {
                $result = self::CONVERT_TYPES[$base];
            } elseif (array_key_exists(strtolower($base), self::CONVERT_TYPES)) {
                $result = self::CONVERT_TYPES[strtolower($base)];
            } elseif (Str::startsWith($string, 'array{')) {
                $result = $this->fromArrayShape($string, $asReference);
            } elseif (Str::startsWith($string, 'array<')) {
                $result = $this->fromSimpleArray($string, $asReference);
            } elseif (Str::startsWith($string, 'int<')) {
                $result = self::TYPE_NUMBER;
            }

            return $result;
        });
    }

    protected function fromArrayShape(string $string, bool $asReference = false): string
    {
        $contents = preg_match('/\{(.+)}/', $string, $matches) ? $matches[1] : $string;
        $suffix   = Str::endsWith($string, '[]') ? '[]' : '';

        if (! Str::contains($string, ':')) {
            $subTypes = explode(',', $contents);
            $subTypes = array_map(fn(string $subType) => $this->getType(trim($subType)), $subTypes);

            if (empty($subTypes)) {
                return self::TYPE_RECORD;
            }

            return sprintf('Record<%s, %s>%s', $subTypes[0], $subTypes[1], $suffix);
        }

        $types = [];

        foreach (explode(',', $contents) as $keyValuePair) {
            [$key, $value] = explode(':', $keyValuePair);

            $types[] = sprintf('%s: %s', trim($key), $this->getType(trim($value), $asReference));
        }

        $spaces     = str_repeat(' ', 2);
        $typeString = $spaces . implode(',' . PHP_EOL . $spaces, $types);

        return sprintf('{%s%s%s}%s', PHP_EOL, $typeString, PHP_EOL, $suffix);
    }

    protected function fromNamespacedClass(string $string, bool $asReference): string
    {
        $hasOwnNamespace = Str::startsWith($string, self::NAMESPACE);

        $newString = Str::after($string, self::NAMESPACE);
        $parts     = array_filter(explode('\\', $newString));
        $parts     = array_map('ucfirst', $parts);

        $implode = static function (array $parts) use ($hasOwnNamespace) {
            $imploded = implode('', $parts);

            if ($hasOwnNamespace && count($parts) > 1 && Str::endsWith($imploded, Arr::first($parts))) {
                $imploded = substr($imploded, strlen((string) Arr::first($parts)));
            }

            return $imploded;
        };

        if ($hasOwnNamespace) {
            $base  = $parts[0];
            $parts = array_slice($parts, 1);
        }

        return ($asReference && $hasOwnNamespace)
            ? sprintf('%s.%s', $base, $implode($parts))
            : $implode($parts);
    }

    protected function fromSimpleArray(string $string, bool $asReference = false): string
    {
        preg_match('/<(.+)>/', $string, $matches);

        $types = array_map(fn(string $string) => $this->getType($string, $asReference), explode(',', $matches[1]));

        $typesString = implode(', ', $types);

        return "Record<$typesString>";
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\TypeScript;

use ArrayIterator;
use DateTime;
use DateTimeImmutable;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;

class TypeParser
{
    public const CONVERT_TYPES = [
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
        'null'                   => TypescriptHelperGenerator::UNDEFINED,
    ];
    private const NAMESPACE    = '\\MyParcelNL\\Pdk\\';

    private $tsTypeCache = [];

    /**
     * @param  string $string
     * @param  bool   $asReference
     *
     * @return string
     */
    public function fromArrayShape(string $string, bool $asReference = false): string
    {
        $contents = preg_match('/\{(.+)}/', $string, $matches) ? $matches[1] : $string;
        $suffix   = Str::endsWith($string, '[]') ? '[]' : '';

        if (! Str::contains($string, ':')) {
            $subTypes = explode(',', $contents);
            $subTypes = array_map(function (string $subType) {
                return $this->getType(trim($subType));
            }, $subTypes);

            if (empty($subTypes)) {
                return 'Record<string, unknown>';
            }

            return sprintf('Record<%s, %s>%s', $subTypes[0], $subTypes[1], $suffix);
        }

        $keyValuePairs = explode(',', $contents);

        $types = [];

        foreach ($keyValuePairs as $keyValuePair) {
            [$key, $value] = explode(':', $keyValuePair);

            $types[] = trim($key) . ': ' . $this->getType(trim((string) $value), $asReference);
        }

        $spaces     = '  ';
        $typeString = $spaces . implode(',' . PHP_EOL . $spaces, $types);

        return sprintf(
            '{%s%s%s}%s',
            PHP_EOL,
            $typeString,
            PHP_EOL,
            $suffix
        );
    }

    /**
     * @param  string $string
     * @param  bool   $asReference
     *
     * @return string
     */
    public function fromNamespacedClass(string $string, bool $asReference): string
    {
        $hasOwnNamespace = Str::startsWith($string, self::NAMESPACE);

        $newString = Str::after($string, self::NAMESPACE);
        $parts     = array_filter(explode('\\', $newString));
        $parts     = array_map('ucfirst', $parts);

        $implode = static function (array $parts) use ($hasOwnNamespace) {
            $imploded = implode('', $parts);

            if ($hasOwnNamespace && count($parts) > 1 && Str::endsWith($imploded, Arr::first($parts))) {
                $imploded = substr($imploded, strlen(Arr::first($parts)));
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

    /**
     * @param  string $string
     * @param  bool   $asReference
     *
     * @return string
     */
    public function fromSimpleArray(string $string, bool $asReference = false): string
    {
        preg_match('/<(.+)>/', $string, $matches);

        $types       = array_map(function (string $string) use ($asReference) {
            return $this->getType($string, $asReference);
        }, explode(',', $matches[1]));
        $typesString = implode(', ', $types);

        return "Record<$typesString>";
    }

    /**
     * @param  string $string
     * @param  bool   $asReference
     *
     * @return string
     */
    public function getType(
        string $string,
        bool   $asReference = false
    ): string {
        $string = trim($string);
        $key    = "{$string}_$asReference";

        if (! isset($this->tsTypeCache[$key])) {
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
            }

            $this->tsTypeCache[$key] = $result;
        }

        return $this->tsTypeCache[$key];
    }
}

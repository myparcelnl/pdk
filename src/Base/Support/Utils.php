<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Str;
use Throwable;

class Utils extends \MyParcelNL\Sdk\src\Helper\Utils
{
    /**
     * @var array
     */
    private static $classBasenameCache = [];

    /**
     * @var array
     */
    private static $classCastCache = [];

    /**
     * @template T
     * @param  class-string<T> $class
     * @param  mixed           ...$args
     *
     * @return T
     */
    public static function cast(string $class, ...$args)
    {
        if (is_a($args[0], $class)) {
            return $args[0];
        }

        try {
            $cacheKey = sprintf('%s-%s', $class, md5(serialize($args)));

            if (! isset(self::$classCastCache[$cacheKey])) {
                self::$classCastCache[$cacheKey] = new $class(...$args);
            }

            return self::$classCastCache[$cacheKey];
        } catch (Throwable $e) {
            // Skip cache if instantiation fails, for example when input contains something that can't be serialized.
            return new $class(...$args);
        }
    }

    /**
     * @param  array    $array
     * @param  null|int $flags
     *
     * @return array
     */
    public static function changeArrayKeysCase(array $array, ?int $flags = null): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            $key = (string) $key;

            if ($value instanceof Arrayable) {
                $value = $value->toArray($flags & Arrayable::RECURSIVE ? $flags : null);
            }

            if ($flags & Arrayable::RECURSIVE && is_array($value)) {
                $value = self::changeArrayKeysCase($value, $flags);
            }

            $newKey            = self::changeCase($key, $flags);
            $newArray[$newKey] = $value;
        }

        return $newArray;
    }

    /**
     * @param  string   $string
     * @param  null|int $flags
     *
     * @return string
     */
    public static function changeCase(string $string, ?int $flags = null): string
    {
        $case = self::getFlagCase($flags);

        return Str::{$case}($string);
    }

    /**
     * Get the class "basename" of the given object / class.
     *
     * @param  mixed $class
     *
     * @return string
     */
    public static function classBasename($class): string
    {
        if (! isset(self::$classBasenameCache[$class])) {
            $class    = is_object($class) ? get_class($class) : $class;
            $lastPart = strrchr('\\' . ltrim($class, '\\'), '\\');

            self::$classBasenameCache[$class] = substr($lastPart, 1);
        }

        return self::$classBasenameCache[$class];
    }

    /**
     * @template T of (null|mixed|object)
     * @param  T $input
     *
     * @return T
     */
    public static function clone($input)
    {
        if (is_object($input)) {
            return clone $input;
        }

        return $input;
    }

    /**
     * @param  string|int $name
     * @param  array      $namesToIdsMap
     *
     * @return null|int
     */
    public static function convertToId($name, array $namesToIdsMap): ?int
    {
        if (is_numeric($name) && in_array((int) $name, $namesToIdsMap, true)) {
            return (int) $name;
        }

        return $namesToIdsMap[$name] ?? null;
    }

    /**
     * @param  string|int $id
     * @param  array      $namesToIdsMap
     *
     * @return null|string
     */
    public static function convertToName($id, array $namesToIdsMap): ?string
    {
        if (! is_numeric($id) && array_key_exists($id, $namesToIdsMap)) {
            return $id;
        }

        return array_search((int) $id, $namesToIdsMap, true) ?: null;
    }

    /**
     * @param  array $array
     *
     * @return array
     */
    public static function filterNull(array $array): array
    {
        return array_filter($array, static function ($value) {
            return null !== $value;
        });
    }

    /**
     * @param $class
     *
     * @return array
     */
    public static function getClassParentsRecursive($class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $nextClass) {
            if ($nextClass !== $class) {
                $results[$nextClass] = $nextClass;
            }

            $results += self::getClassTraitsRecursive($nextClass);
        }

        return array_unique($results);
    }

    /**
     * @param $trait
     *
     * @return array|false|string[]
     */
    public static function getClassTraitsRecursive($trait)
    {
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $nextTrait) {
            $traits += self::getClassTraitsRecursive($nextTrait);
        }

        return $traits;
    }

    /**
     * @param  array $previous
     * @param  array $current
     *
     * @return array
     */
    public static function mergeArraysIgnoringNull(array $previous, array $current): array
    {
        $keys = array_keys($current);

        foreach ($keys as $key) {
            if (is_array($current[$key])) {
                $current[$key] = self::mergeArraysIgnoringNull($previous[$key] ?? [], $current[$key]);
            }

            if (null !== $current[$key]) {
                continue;
            }

            $current[$key] = $previous[$key] ?? null;
        }

        return $current + $previous;
    }

    /**
     * @param  string|string[] $value
     *
     * @return array
     */
    public static function toArray($value): array
    {
        return array_reduce(Arr::wrap($value), static function (array $acc, $item) {
            if (is_scalar($item)) {
                $ids = explode(';', (string) $item);
                array_push($acc, ...$ids);
            }

            return $acc;
        }, []);
    }

    /**
     * @param  array $array
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public static function toRecursiveCollection(array $array): Collection
    {
        $collection = new Collection($array);

        $collection->each(function ($value, $key) use ($collection) {
            if (! is_array($value)) {
                return;
            }

            $collection->put($key, self::toRecursiveCollection($value));
        });

        return $collection;
    }

    /**
     * @param  null|int $flags
     *
     * @return string
     */
    private static function getFlagCase(?int $flags = null): string
    {
        if ($flags & Arrayable::CASE_SNAKE) {
            return 'snake';
        }

        if ($flags & Arrayable::CASE_KEBAB) {
            return 'kebab';
        }

        if ($flags & Arrayable::CASE_STUDLY) {
            return 'studly';
        }

        return 'camel';
    }
}

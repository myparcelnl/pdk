<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Sdk\src\Support\Str;

class Utils extends \MyParcelNL\Sdk\src\Helper\Utils
{
    /**
     * @param  string $class
     * @param  mixed  ...$args
     *
     * @return mixed
     */
    public static function cast(string $class, ...$args)
    {
        if (is_a($args[0], $class)) {
            return $args[0];
        }

        return new $class(...$args);
    }

    /**
     * @param  array       $array
     * @param  string|null $case
     *
     * @return array
     */
    public static function changeArrayKeysCase(array $array, string $case = null): array
    {
        $newArray = [];

        foreach ($array as $key => $value) {
            $newArray[Str::{$case ?? 'camel'}($key)] = $value;
        }

        return $newArray;
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
        $class    = is_object($class) ? get_class($class) : $class;
        $lastPart = strrchr('\\' . ltrim($class, '\\'), '\\');

        return substr($lastPart, 1);
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
}

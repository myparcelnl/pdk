<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use MyParcelNL\Sdk\src\Support\Str;

class Utils extends \MyParcelNL\Sdk\src\Helper\Utils
{
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
}

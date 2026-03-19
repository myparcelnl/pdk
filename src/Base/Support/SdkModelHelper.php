<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Support;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\ModelInterface;

/**
 * Utility for interacting with auto-generated SDK model classes that implement the OpenAPI ModelInterface pattern.
 * These models use a $container array with snake_case keys, and expose static getters()/setters()/attributeMap() methods.
 */
class SdkModelHelper
{
    /**
     * Convert a snake_case SDK property name to camelCase PDK attribute name.
     *
     * @param  string $snakeCaseKey
     *
     * @return string
     */
    public static function toPdkCase(string $snakeCaseKey): string
    {
        return Utils::changeCase($snakeCaseKey);
    }

    /**
     * Convert a camelCase PDK attribute name to snake_case SDK property name.
     *
     * @param  string $camelCaseKey
     *
     * @return string
     */
    public static function toSdkCase(string $camelCaseKey): string
    {
        return Utils::changeCase($camelCaseKey, Arrayable::CASE_SNAKE);
    }

    /**
     * Build a map of camelCase PDK key => getter method name for an SDK model.
     *
     * @param  object|string $classOrInstance
     *
     * @return array<string, string> ['packageTypes' => 'getPackageTypes', ...]
     */
    public static function buildGetterMap($classOrInstance): array
    {
        $class   = is_object($classOrInstance) ? get_class($classOrInstance) : $classOrInstance;
        $getters = $class::getters();
        $map     = [];

        foreach ($getters as $snakeKey => $getterMethod) {
            $camelKey       = self::toPdkCase($snakeKey);
            $map[$camelKey] = $getterMethod;
        }

        return $map;
    }

    /**
     * Build a map of camelCase PDK key => setter method name for an SDK model.
     *
     * @param  object|string $classOrInstance
     *
     * @return array<string, string> ['packageTypes' => 'setPackageTypes', ...]
     */
    public static function buildSetterMap($classOrInstance): array
    {
        $class   = is_object($classOrInstance) ? get_class($classOrInstance) : $classOrInstance;
        $setters = $class::setters();
        $map     = [];

        foreach ($setters as $snakeKey => $setterMethod) {
            $camelKey       = self::toPdkCase($snakeKey);
            $map[$camelKey] = $setterMethod;
        }

        return $map;
    }

    /**
     * Serialize an SDK model instance to an associative array with camelCase keys.
     *
     * @param  object $sdkModel
     *
     * @return array
     */
    public static function toArray(object $sdkModel): array
    {
        $getterMap = self::buildGetterMap($sdkModel);
        $result    = [];

        foreach ($getterMap as $camelKey => $getterMethod) {
            $value = $sdkModel->{$getterMethod}();

            if (null !== $value) {
                $result[$camelKey] = self::serializeValue($value);
            }
        }

        return $result;
    }

    /**
     * Recursively serialize a value from an SDK model.
     *
     * @param  mixed $value
     *
     * @return mixed
     */
    private static function serializeValue($value)
    {
        if (is_object($value) && $value instanceof ModelInterface) {
            return self::toArray($value);
        }

        if (is_array($value)) {
            return array_map([self::class, 'serializeValue'], $value);
        }

        if ($value instanceof \JsonSerializable) {
            return $value->jsonSerialize();
        }

        return $value;
    }
}

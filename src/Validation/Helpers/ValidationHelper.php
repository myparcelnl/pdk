<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Helpers;

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Sdk\src\Support\Arr;

class ValidationHelper
{
    public const     AGE_CHECK         = 'ageCheck';
    public const     CARRIER           = 'carrier';
    public const     CC                = 'cc';
    public const     DATA              = 'data';
    public const     ENUM              = 'enum';
    public const     ID                = 'id';
    public const     LABEL_DESCRIPTION = 'labelDescription';
    public const     LARGE_FORMAT      = 'largeFormat';
    public const     LOCATION_CODE     = 'locationCode';
    public const     NAME              = 'name';
    public const     OPTIONS           = 'options';
    public const     REQUIREMENTS      = 'requirements';
    public const     SHIPPING_ZONE     = 'shippingZone';
    public const     WEIGHT            = 'weight';

    /**
     * @var array
     */
    private $mappedArray;

    /**
     * @param  string $needle
     * @param  array  $haystack
     * @param  array  $path
     *
     * @return array|false
     */
    public function getArrayPath(string $needle, array $haystack, array $path = [])
    {
        foreach ($haystack as $key => $value) {
            $currentPath = array_merge($path, [$key]);

            if (is_array($value) && $result = self::getArrayPath($needle, $value, $currentPath)) {
                return $result;
            }

            if ($value === $needle) {
                return $currentPath;
            }
        }
        return false;
    }

    /**
     * @param  string $value
     * @param  string $column
     * @param  array  $array
     *
     * @return array
     */
    public function getIndexByValue(string $value, string $column, array $array): array
    {
        $index = array_search(
            $value,
            array_column($array, $column),
            true
        );

        return $array[$index];
    }

    /**
     * @param $order
     *
     * @return null|string
     */
    public function getShippingZone($order): ?string
    {
        $countryCode = $order->recipient->cc;

        if (null===$countryCode) {
            return null;
        }

        if (CountryCodes::CC_NL === $countryCode) {
            return CountryCodes::CC_NL;
        }

        if (CountryCodes::CC_BE === $countryCode) {
            return CountryCodes::CC_BE;
        }

        if (in_array($countryCode, CountryCodes::EU_COUNTRIES, true)) {
            return CountryCodes::ZONE_EU;
        }

        return CountryCodes::ZONE_ROW;
    }

    /**
     * @param  int   $value
     * @param  array $properties
     *
     * @return bool
     */
    public function isValueWithinBoundaries(int $value, array $properties): bool
    {
        return $value >= $properties['minimum'] && $value <= $properties['maximum'];
    }

    /**
     * @param  array $data
     *
     * @return array
     */
    public function mergeDeliveryDataIntoPackageData(array $data): array
    {
        $deliveryTypeData = $data['deliveryType'][self::DATA] ?? [];
        $packageTypeData  = $data['packageType'][self::DATA] ?? [];

        $this->mappedArray['packageType']  = $packageTypeData;
        $this->mappedArray['deliveryType'] = $deliveryTypeData;

        $toCheck = [self::OPTIONS, self::REQUIREMENTS];

        foreach ($toCheck as $index) {
            if (array_key_exists($index, $deliveryTypeData)) {
                $this->mappedArray[$index] = $this->replaceItems(
                    $packageTypeData,
                    $deliveryTypeData,
                    $index
                )[$index];

                $this->mappedArray[$index] = array_merge($packageTypeData[$index], $this->mappedArray[$index]);
            } else {
                $this->mappedArray[$index] = $packageTypeData[$index];
            }
        }

        return $this->mappedArray;
    }

    /**
     * @param  array $mappedArray
     * @param  array $tempArray
     *
     * @return array
     */
    public function mergeOptions(array $mappedArray, array $tempArray): array
    {
        $this->mappedArray = $mappedArray;

        $data = array_map(static function ($index) {
            return (new Collection($index))->reduce(function ($acc, $item) {
                return Utils::mergeArraysIgnoringNull($acc, $item);
            }, []);
        }, $tempArray);

        return $this->mergeDeliveryDataIntoPackageData($data);
    }

    /**
     * @param  array $validationSchemaCopy
     * @param  array $path
     *
     * @return array
     */
    public function removeFromCopySchema(array $validationSchemaCopy, array $path): array
    {
        array_pop($path);
        $implode = implode('.', $path);

        Arr::forget($validationSchemaCopy, $implode);

        return $validationSchemaCopy;
    }

    /**
     * @param  array  $packageTypeData
     * @param  array  $deliveryTypeData
     * @param  string $index
     *
     * @return array
     */
    private function replaceItems(array $packageTypeData, array $deliveryTypeData, string $index): array
    {
        foreach ($deliveryTypeData[$index] as $key => $item) {
            $packageTypeData[$index][$key] = $item;
        }

        return $packageTypeData;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Service;

use InvalidArgumentException;
use MyParcelNL\Sdk\src\Exception\ValidationException;
use MyParcelNL\Sdk\src\Support\Arr;

class WeightService
{
    public const UNIT_GRAMS           = 'g';
    public const UNIT_KILOGRAMS       = 'kg';
    public const UNIT_OUNCES          = 'oz';
    public const UNIT_POUNDS          = 'lbs';
    public const DIGITAL_STAMP_RANGES = [
        [
            'min'     => 0,
            'max'     => 20,
            'average' => 15,
        ],
        [
            'min'     => 20,
            'max'     => 50,
            'average' => 35,
        ],
        [
            'min'     => 50,
            'max'     => 100,
            'average' => 75,
        ],
        [
            'min'     => 100,
            'max'     => 350,
            'average' => 225,
        ],
        [
            'min'     => 350,
            'max'     => 2000,
            'average' => 1175,
        ],
    ];

    /**
     * Converts a weight in grams to a digital stamp category weight, by default the PostNL categories are used.
     *
     * @param int   $weight in grams.
     * @param array $ranges optional your own categories, must be formatted like self::DIGITAL_STAMP_RANGES.
     *
     * @return int
     * @throws \MyParcelNL\Sdk\src\Exception\ValidationException when supplied weight is larger than max weight.
     */
    public static function convertToDigitalStamp(int $weight, array $ranges = self::DIGITAL_STAMP_RANGES): int
    {
        if ($weight > Arr::last($ranges)['max']) {
            throw new ValidationException(sprintf(
                'Supplied weight to convert of %sg exceeds maximum digital stamp weight of %sg',
                $weight,
                Arr::last($ranges)['max']
            ));
        }

        $results = Arr::where(
            $ranges,
            static function ($range) use ($weight) {
                return $weight > $range['min'];
            }
        );

        if (empty($results)) {
            $digitalStampRangeWeight = Arr::first($ranges)['average'];
        } else {
            $digitalStampRangeWeight = Arr::last($results)['average'];
        }

        return $digitalStampRangeWeight;
    }

    /**
     * Returns the weight in grams.
     *
     * @param  int|float $weight
     * @param  string    $unit
     *
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    public static function convertToGrams($weight, string $unit): int
    {
        $floatWeight = (float) $weight;

        switch ($unit) {
            case self::UNIT_GRAMS:
                break;
            case self::UNIT_KILOGRAMS:
                $weight = $floatWeight * 1000;
                break;
            case self::UNIT_POUNDS:
                $weight = $floatWeight * 453.59237;
                break;
            case self::UNIT_OUNCES:
                $weight = $floatWeight * 28.34952;
                break;
            default:
                throw new InvalidArgumentException('Unknown weight unit passed: ' . $unit);
        }

        return (int) ceil($weight);
    }
}

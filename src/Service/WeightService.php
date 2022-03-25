<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Service;

use InvalidArgumentException;

class WeightService
{
    public const UNIT_GRAMS     = 'g';
    public const UNIT_KILOGRAMS = 'kg';
    public const UNIT_OUNCES    = 'oz';
    public const UNIT_POUNDS    = 'lbs';

    /**
     * Returns the weight in grams.
     *
     * @param  int|float $weight
     * @param  string    $unit
     *
     * @return int
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
                $weight = $floatWeight / 0.45359237;
                break;
            case self::UNIT_OUNCES:
                $weight = $floatWeight / 0.0283495231;
                break;
            default:
                throw new InvalidArgumentException('Unknown weight unit passed: ' . $unit);
        }

        return (int) ceil($weight);
    }
}

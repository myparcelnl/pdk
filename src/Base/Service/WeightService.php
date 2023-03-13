<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;

class WeightService implements WeightServiceInterface
{
    /**
     * @param  int   $weight
     * @param  array $ranges
     *
     * @return int
     */
    public function convertToDigitalStamp(int $weight, array $ranges = self::DIGITAL_STAMP_RANGES): int
    {
        if ($weight > Arr::last($ranges)['max']) {
            throw new InvalidArgumentException(
                sprintf(
                    'Supplied weight to convert of %sg exceeds maximum digital stamp weight of %sg',
                    $weight,
                    Arr::last($ranges)['max']
                )
            );
        }

        $results = Arr::where($ranges, static function ($range) use ($weight) {
            return $weight > $range['min'];
        });

        if (empty($results)) {
            $rangeWeight = Arr::first($ranges)['average'];
        } else {
            $rangeWeight = Arr::last($results)['average'];
        }

        return $rangeWeight;
    }

    /**
     * @param  int|float $weight
     * @param  string    $unit
     *
     * @return int
     */
    public function convertToGrams($weight, string $unit): int
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

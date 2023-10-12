<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;

class WeightService implements WeightServiceInterface
{
    /**
     * @param  int   $weight
     * @param  array $ranges
     *
     * @return int
     */
    public function convertToDigitalStamp(int $weight, array $ranges = []): int
    {
        if (empty($ranges)) {
            $ranges = Pdk::get('digitalStampRanges');
        }

        $lastRange = Arr::last($ranges);

        if ($weight > $lastRange['max']) {
            throw new InvalidArgumentException(
                sprintf(
                    'Supplied weight to convert of %sg exceeds maximum digital stamp weight of %sg',
                    $weight,
                    $lastRange['max']
                )
            );
        }

        $results = Arr::where($ranges, static function ($range) use ($weight) {
            return $weight > $range['min'];
        });

        return empty($results)
            ? Arr::first($ranges)['average']
            : Arr::last($results)['average'];
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

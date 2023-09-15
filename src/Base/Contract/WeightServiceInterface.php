<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface WeightServiceInterface
{
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
    public const UNIT_GRAMS           = 'g';
    public const UNIT_KILOGRAMS       = 'kg';
    public const UNIT_OUNCES          = 'oz';
    public const UNIT_POUNDS          = 'lbs';

    /**
     * Convert a weight into a digital stamp range.
     *
     * @param  int   $weight - Weight in grams.
     * @param  array $ranges - Ranges to convert to.
     */
    public function convertToDigitalStamp(int $weight, array $ranges = self::DIGITAL_STAMP_RANGES): int;

    /**
     * Convert a given weight to grams.
     *
     * @param  int|float $weight - Weight in the unit specified in $unit
     * @param  string    $unit   - Unit of the weight
     */
    public function convertToGrams($weight, string $unit): int;
}

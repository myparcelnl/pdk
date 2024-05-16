<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

use MyParcelNL\Pdk\Shipment\Model\PackageType;

interface WeightServiceInterface
{
    /**
     * @deprecated use Pdk::get('digitalStampRanges'). Will be removed in v3.0.0
     */
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
     *
     * @return int
     */
    public function convertToDigitalStamp(int $weight, array $ranges = []): int;

    /**
     * Convert a given weight to grams.
     *
     * @param  int|float $weight - Weight in the unit specified in $unit
     * @param  string    $unit   - Unit of the weight
     *
     * @return int
     */
    public function convertToGrams($weight, string $unit): int;

    /**
     * @param  int                                        $weight
     * @param  \MyParcelNL\Pdk\Shipment\Model\PackageType $packageType
     *
     * @return int
     */
    public function addEmptyPackageWeight(int $weight, PackageType $packageType): int;
}

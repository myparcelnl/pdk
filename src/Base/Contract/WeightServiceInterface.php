<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

use MyParcelNL\Pdk\Shipment\Model\PackageType;

interface WeightServiceInterface
{
    public const UNIT_GRAMS     = 'g';
    public const UNIT_KILOGRAMS = 'kg';
    public const UNIT_OUNCES    = 'oz';
    public const UNIT_POUNDS    = 'lbs';

    /**
     * @param  int                                        $weight
     * @param  \MyParcelNL\Pdk\Shipment\Model\PackageType $packageType
     *
     * @return int
     */
    public function addEmptyPackageWeight(int $weight, PackageType $packageType): int;

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
}

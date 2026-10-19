<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Order\Model\PdkPhysicalProperties;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\PackageType;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefShipmentPackageTypeV2;
use MyParcelNL\Sdk\Services\Mapping\ApiMapperService;

class WeightService implements WeightServiceInterface
{
    private const PACKAGE_TYPE_EMPTY_WEIGHT_MAP = [
        RefShipmentPackageTypeV2::PACKAGE       => OrderSettings::EMPTY_PARCEL_WEIGHT,
        RefShipmentPackageTypeV2::SMALL_PACKAGE => OrderSettings::EMPTY_PACKAGE_SMALL_WEIGHT,
        RefShipmentPackageTypeV2::MAILBOX       => OrderSettings::EMPTY_MAILBOX_WEIGHT,
        RefShipmentPackageTypeV2::DIGITAL_STAMP => OrderSettings::EMPTY_DIGITAL_STAMP_WEIGHT,
    ];

    /**
     * @param  int                                        $weight
     * @param  \MyParcelNL\Pdk\Shipment\Model\PackageType $packageType
     *
     * @return int
     */
    public function addEmptyPackageWeight(int $weight, PackageType $packageType): int
    {
        $fullWeight = $weight + $this->getEmptyWeightForPackageType($packageType);

        return $fullWeight ?: 1;
    }

    /**
     * Resolve the effective shipping weight for a given package type.
     *
     * The empty-weight fallback (configured per package type via OrderSettings) is added
     * only when the merchant has NOT manually set a weight on the order — i.e. when
     * `manualWeight === TriStateService::INHERIT`. When a manual weight is present, that
     * value is the explicit shipping weight and no fallback is applied.
     *
     * Mirrors the rules in {@see \MyParcelNL\Pdk\App\Order\Calculator\General\WeightCalculator}
     * so capability checks performed before WeightCalculator runs see the same value the
     * export will eventually submit.
     *
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkPhysicalProperties $physicalProperties
     * @param  \MyParcelNL\Pdk\Shipment\Model\PackageType            $packageType
     *
     * @return int Weight in grams, never less than 1 (the API rejects weight=0).
     */
    public function getEffectiveWeight(PdkPhysicalProperties $physicalProperties, PackageType $packageType): int
    {
        $weight = (int) $physicalProperties->totalWeight;

        if (TriStateService::INHERIT === $physicalProperties->manualWeight) {
            $weight += $this->getEmptyWeightForPackageType($packageType);
        }

        return $weight ?: 1;
    }

    /**
     * Look up the merchant-configured empty weight (in grams) for the given package type.
     *
     * Reads the OrderSettings entry mapped to the package type name (e.g. mailbox →
     * `emptyMailboxWeight`). Returns 0 when the package type has no mapping (UNFRANKED,
     * unknown types) or when the setting is unset.
     *
     * Shared between {@see addEmptyPackageWeight} (always-add semantics, used by the cart
     * checkout path) and {@see getEffectiveWeight} (manualWeight-aware, used by the order
     * calculator pipeline).
     */
    private function getEmptyWeightForPackageType(PackageType $packageType): int
    {
        $v2PackageType      = ApiMapperService::forPackageType()->v2NameFromLegacyName((string) $packageType->name);
        $emptyWeightSetting = self::PACKAGE_TYPE_EMPTY_WEIGHT_MAP[$v2PackageType] ?? null;

        if (! $emptyWeightSetting) {
            return 0;
        }

        return (int) Settings::get($emptyWeightSetting, OrderSettings::ID);
    }

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

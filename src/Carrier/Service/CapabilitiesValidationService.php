<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Service;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2;

/**
 * Business logic for validating carrier capabilities.
 *
 * Answers questions like "can this carrier handle this weight?" and "which package
 * type has the highest weight limit?" using data from the capabilities API.
 *
 * Reusable across checkout, order export, and admin UI — does not depend on
 * cart, settings, or frontend concerns.
 */
class CapabilitiesValidationService
{
    /**
     * @var \MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository
     */
    private $capabilitiesRepository;

    public function __construct(CarrierCapabilitiesRepository $capabilitiesRepository)
    {
        $this->capabilitiesRepository = $capabilitiesRepository;
    }

    /**
     * @return \MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository
     */
    public function getRepository(): CarrierCapabilitiesRepository
    {
        return $this->capabilitiesRepository;
    }

    /**
     * Index a capabilities response array by carrier name.
     *
     * @param  RefCapabilitiesResponseCapabilityV2[] $capabilities
     *
     * @return array<string, RefCapabilitiesResponseCapabilityV2>
     */
    public function indexByCarrier(array $capabilities): array
    {
        $indexed = [];
        foreach ($capabilities as $capability) {
            $indexed[$capability->getCarrier()] = $capability; // @phpstan-ignore-line SDK declares enum type but returns string
        }

        return $indexed;
    }

    /**
     * Fetch max weight for each package type from capabilities.
     *
     * Returns null for types where the API does not define a max weight constraint.
     *
     * @param  string $cc           ISO 3166-1 alpha-2 country code
     * @param  array  $allowedTypes PDK name => V2 name
     *
     * @return array<string, null|int> PDK package type name => max weight in grams, or null if unconstrained
     */
    public function getPackageTypeWeights(string $cc, array $allowedTypes): array
    {
        $weights = [];

        foreach ($allowedTypes as $packageTypeName => $v2PackageType) {
            $capabilities = $this->indexByCarrier(
                $this->capabilitiesRepository->getCapabilities([
                    'recipient'    => ['country_code' => $cc],
                    'package_type' => $v2PackageType,
                ])
            );
            $weights[$packageTypeName] = $this->getHighestMaxWeight($capabilities);
        }

        return $weights;
    }

    /**
     * Whether the given weight (grams) fits within the capability's weight constraints.
     *
     * Min and max are both checked when present. Capabilities without a weight constraint
     * accept any weight.
     *
     * @param  RefCapabilitiesResponseCapabilityV2 $capability
     * @param  int                                 $weight Weight in grams
     *
     * @return bool
     */
    public function capabilitySupportsWeight($capability, int $weight): bool
    {
        // Defensive null checks: the SDK PHPDoc declares these as non-nullable, but the API
        // may omit fields at runtime. PHPStan warnings are suppressed for this reason.
        $physicalProperties = $capability->getPhysicalProperties();

        if (! $physicalProperties) { // @phpstan-ignore-line
            return true;
        }

        $weightConstraint = $physicalProperties->getWeight();

        if (! $weightConstraint) {
            return true;
        }

        $min = $weightConstraint->getMin() ? (int) $weightConstraint->getMin()->getValue() : null; // @phpstan-ignore-line
        $max = $weightConstraint->getMax() ? (int) $weightConstraint->getMax()->getValue() : null; // @phpstan-ignore-line

        return ($max === null || $weight <= $max) // @phpstan-ignore-line
            && ($min === null || $weight >= $min); // @phpstan-ignore-line
    }

    /**
     * Build an insurance-tier ladder for a min/max range.
     *
     * Includes fine-grained floor tiers (€100, €250, €500) at the low end so that
     * low-value orders can still be insured at realistic increments, then €500 steps
     * for higher amounts. Mirrors the curated ladders that lived in the per-carrier
     * JSON schemas before INT-1501.
     *
     * @param  int $min Minimum amount in cents
     * @param  int $max Maximum amount in cents
     *
     * @return int[] Sorted, unique tier amounts in cents, including min and max
     */
    public static function buildInsuranceTiers(int $min, int $max): array
    {
        if ($min >= $max) {
            return [$min];
        }

        $tiers = [$min];

        // Floor tiers between min and max (exclusive bounds): €100, €250, €500.
        foreach ([10_000, 25_000, 50_000] as $tier) {
            if ($tier > $min && $tier < $max) {
                $tiers[] = $tier;
            }
        }

        // €500 steps from the next round €500 boundary up to (but excluding) max.
        $stepStart = max($min, 50_000) + 50_000;
        for ($t = $stepStart; $t < $max; $t += 50_000) {
            $tiers[] = $t;
        }

        $tiers[] = $max;

        return array_values(array_unique($tiers));
    }

    /**
     * Pick the heaviest package type among the given types, based on capabilities weight data.
     *
     * null weight = unconstrained = heavier than any defined weight.
     *
     * @param  string[] $types       PDK package type names
     * @param  array    $typeWeights PDK name => max weight (null = unconstrained)
     *
     * @return string PDK package type name
     */
    public function resolveHeaviestType(array $types, array $typeWeights): string
    {
        $desired  = null;
        $heaviest = null;

        foreach ($types as $type) {
            if (! array_key_exists($type, $typeWeights)) {
                continue;
            }

            $weight = $typeWeights[$type];

            if ($desired === null || Utils::compareNullableInts($weight, $heaviest) > 0) {
                $heaviest = $weight;
                $desired  = $type;
            }
        }

        return $desired ?? DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME;
    }

    /**
     * Get the highest defined max weight across all carriers in a capabilities response.
     *
     * Carriers without a defined max weight are skipped — they don't cap the result,
     * but other carriers' defined maxes still contribute. Returns null only when no
     * carrier in the response defines a max weight at all.
     *
     * @param  array $capabilities
     *
     * @return null|int Max weight in grams, or null if no carrier defines a max weight
     */
    private function getHighestMaxWeight(array $capabilities): ?int
    {
        $maxWeight = null;

        foreach ($capabilities as $capability) {
            $props = $capability->getPhysicalProperties();

            if (! $props || ! $props->getWeight() || ! $props->getWeight()->getMax()) {
                continue;
            }

            $carrierMax = (int) $props->getWeight()->getMax()->getValue();
            $maxWeight  = $maxWeight === null ? $carrierMax : max($maxWeight, $carrierMax);
        }

        return $maxWeight;
    }
}

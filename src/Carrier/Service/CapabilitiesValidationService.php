<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Service;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Repository\CarrierCapabilitiesRepository;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostCapabilitiesRequestV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2;

/**
 * Business logic for validating carrier capabilities.
 *
 * Answers questions like "can this carrier handle this weight?" and "which package
 * type has the highest weight limit?" using data from the capabilities API.
 *
 * Reusable across checkout, order export, and admin UI — does not depend on
 * cart or frontend state. Reads CarrierSettings to skip carriers without
 * delivery options enabled when aggregating per-package-type weights.
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
     * By default, only carriers with delivery options enabled in shop settings
     * contribute to the per-type aggregation — disabled carriers are not available
     * in checkout and should not affect package-type ordering. Pass
     * $filterByEnabledCarriers = false for use cases that need the raw API view
     * across all carriers the merchant has access to.
     *
     * @param  string $cc                       ISO 3166-1 alpha-2 country code
     * @param  array  $allowedTypes             PDK name => V2 name
     * @param  bool   $filterByEnabledCarriers  When true, exclude carriers without
     *                                          CarrierSettings::DELIVERY_OPTIONS_ENABLED
     *
     * @return array<string, null|int> PDK package type name => max weight in grams, or null if unconstrained
     */
    public function getPackageTypeWeights(
        string $cc,
        array $allowedTypes,
        bool $filterByEnabledCarriers = true
    ): array {
        $enabledCarriers = $filterByEnabledCarriers ? $this->getEnabledCarrierNames() : null;
        $weights         = [];

        foreach ($allowedTypes as $packageTypeName => $v2PackageType) {
            $capabilities = $this->indexByCarrier(
                $this->capabilitiesRepository->getCapabilities([
                    'recipient'    => ['country_code' => $cc],
                    'package_type' => $v2PackageType,
                ])
            );

            if ($enabledCarriers !== null) {
                $capabilities = array_intersect_key($capabilities, array_flip($enabledCarriers));
            }

            $weights[$packageTypeName] = $this->getHighestMaxWeight($capabilities);
        }

        return $weights;
    }

    /**
     * V2 carrier names for which delivery options are enabled in shop settings.
     *
     * @return string[]
     */
    private function getEnabledCarrierNames(): array
    {
        $carrierSettings = Settings::get(CarrierSettings::ID) ?? [];

        return array_keys(
            array_filter(
                $carrierSettings,
                static function ($settings): bool {
                    return ! empty($settings[CarrierSettings::DELIVERY_OPTIONS_ENABLED]);
                }
            )
        );
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
    public function supportsWeight($capability, int $weight): bool
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
     * Whether the carrier supports inbound (return) shipments to the given destination.
     *
     * The capabilities endpoint requires a destination country code to answer this
     * question; "does carrier X support returns at all?" is an API gap (contract
     * definitions do not advertise return support carrier-wide).
     *
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     * @param  string                                $countryCode ISO 3166-1 alpha-2 destination country code
     *
     * @return bool
     */
    public function supportsReturns(Carrier $carrier, string $countryCode): bool
    {
        $capabilities = $this->capabilitiesRepository->getCapabilities([
            'carrier'   => $carrier->carrier,
            'direction' => CapabilitiesPostCapabilitiesRequestV2::DIRECTION_INBOUND,
            'recipient' => ['country_code' => $countryCode],
        ]);

        return ! empty($capabilities);
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

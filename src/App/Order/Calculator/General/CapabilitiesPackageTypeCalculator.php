<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\PackageType;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesResponseCapabilityV2;

/**
 * Validates the order's package type against carrier capabilities and falls back
 * to the next available type when the selected type is not supported.
 */
final class CapabilitiesPackageTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var \MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService
     */
    private $capabilitiesService;

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface
     */
    private $countryService;

    /**
     * @var \MyParcelNL\Pdk\Base\Contract\WeightServiceInterface
     */
    private $weightService;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->capabilitiesService = Pdk::get(CapabilitiesValidationService::class);
        $this->countryService      = Pdk::get(CountryServiceInterface::class);
        $this->weightService       = Pdk::get(WeightServiceInterface::class);
    }

    /**
     * @return void
     */
    public function calculate(): void
    {
        $carrier       = $this->order->deliveryOptions->carrier;
        $cc            = $this->order->shippingAddress->cc;
        $currentType   = $this->order->deliveryOptions->packageType;
        $v2CurrentType = DeliveryOptions::PACKAGE_TYPES_V2_MAP[$currentType] ?? null;

        if (! $cc || ! $v2CurrentType) {
            return;
        }

        // Cache key: cc+package_type — shareable across orders/carriers. Carrier and
        // weight are filtered client-side so the cache stays warm regardless of order weight.
        $currentCapability = $this->capabilitiesService->indexByCarrier(
            $this->capabilitiesService->getRepository()->getCapabilities([
                'recipient'    => ['country_code' => $cc],
                'package_type' => $v2CurrentType,
            ])
        )[$carrier->carrier] ?? null;

        // Use the weight that the export will actually send: raw order weight plus the
        // configured empty-weight fallback for THIS package type, but only when the
        // merchant didn't manually set a weight (mirrors WeightCalculator's rules).
        $weightForCurrent = $this->effectiveWeightFor($currentType);

        $supported = $currentCapability
            && $this->capabilitiesService->capabilitySupportsWeight($currentCapability, $weightForCurrent)
            && ! $this->isInternationalMailboxBlocked($currentType, $cc, $carrier);

        if ($supported) {
            return;
        }

        $this->fallbackToNextAvailableType($cc, $carrier);
    }

    /**
     * Check whether the current package type is an international mailbox that the merchant has not enabled.
     *
     * @param  string                              $currentType
     * @param  string                              $cc
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return bool
     */
    private function isInternationalMailboxBlocked(string $currentType, string $cc, Carrier $carrier): bool
    {
        if ($currentType !== DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME) {
            return false;
        }

        if ($this->countryService->isLocalCountry($cc)) {
            return false;
        }

        $carrierSettings = CarrierSettings::fromCarrier($carrier);

        return ! $carrierSettings->allowInternationalMailbox;
    }

    /**
     * Fall back to the next available package type, sorted by this carrier's max
     * weight per type ascending — picks the smallest type that still fits. Each
     * candidate is evaluated against ITS OWN effective weight (raw order weight +
     * the empty-weight setting configured for that type), so capability min/max
     * checks match what the export will actually submit.
     *
     * Only iterates types the carrier itself declares support for via its contract
     * definitions; querying capability data for types the carrier never supports
     * would be wasted API calls.
     */
    private function fallbackToNextAvailableType(string $cc, Carrier $carrier): void
    {
        $availableByType = [];
        $v2ToPdkName     = array_flip(DeliveryOptions::PACKAGE_TYPES_V2_MAP);

        foreach (($carrier->packageTypes ?? []) as $v2Name) {
            $pdkName = $v2ToPdkName[$v2Name] ?? null;

            if ($pdkName === null || $this->isInternationalMailboxBlocked($pdkName, $cc, $carrier)) {
                continue;
            }

            $capability = $this->capabilitiesService->indexByCarrier(
                $this->capabilitiesService->getRepository()->getCapabilities([
                    'recipient'    => ['country_code' => $cc],
                    'package_type' => $v2Name,
                ])
            )[$carrier->carrier] ?? null;

            $effectiveWeight = $this->effectiveWeightFor($pdkName);

            if ($capability && $this->capabilitiesService->capabilitySupportsWeight($capability, $effectiveWeight)) {
                $availableByType[$pdkName] = $capability;
            }
        }

        $typeNames = array_keys($availableByType);
        usort($typeNames, static function (string $a, string $b) use ($availableByType): int {
            return Utils::compareNullableInts(
                self::extractMaxWeight($availableByType[$a]),
                self::extractMaxWeight($availableByType[$b])
            );
        });

        $this->order->deliveryOptions->packageType = ! empty($typeNames)
            ? $typeNames[0]
            : DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME;
    }

    /**
     * Effective shipping weight if the order were exported as the given package type.
     * Delegates to the same rule WeightCalculator uses (mind manualWeight tristate).
     */
    private function effectiveWeightFor(string $pdkPackageTypeName): int
    {
        return $this->weightService->getEffectiveWeight(
            $this->order->physicalProperties,
            new PackageType(['name' => $pdkPackageTypeName])
        );
    }

    /**
     * Extract the max weight constraint (grams) from a capability, or null when unconstrained.
     */
    private static function extractMaxWeight(RefCapabilitiesResponseCapabilityV2 $capability): ?int
    {
        $physicalProperties = $capability->getPhysicalProperties();
        if (! $physicalProperties) { // @phpstan-ignore-line SDK declares non-nullable but API may omit
            return null;
        }

        $weightConstraint = $physicalProperties->getWeight();
        if (! $weightConstraint) {
            return null;
        }

        $max = $weightConstraint->getMax();

        return $max ? (int) $max->getValue() : null; // @phpstan-ignore-line SDK declares non-nullable but API may omit
    }
}

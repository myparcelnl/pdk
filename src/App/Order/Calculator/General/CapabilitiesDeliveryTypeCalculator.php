<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesDeliveryTypeV2;

/**
 * Validates the order's delivery type against carrier capabilities and resets
 * to a supported type when the selected one is not available for the current
 * carrier + country + package type combination.
 *
 * Replaces the per-carrier delivery-type calculators (PostNL/DhlForYou/UPS)
 * deleted in INT-1501 — same intent, capabilities-driven instead of hardcoded.
 */
final class CapabilitiesDeliveryTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var \MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService
     */
    private $capabilitiesService;

    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->capabilitiesService = Pdk::get(CapabilitiesValidationService::class);
    }

    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;
        $carrier         = $deliveryOptions->carrier;
        $cc              = $this->order->shippingAddress->cc;
        $v2PackageType   = DeliveryOptions::PACKAGE_TYPES_V2_MAP[$deliveryOptions->packageType] ?? null;

        if (! $cc || ! $v2PackageType) {
            return;
        }

        // Cache key: cc+carrier+package_type — same shape as the package-type calc's calls.
        $capabilities = $this->capabilitiesService->getRepository()->getCapabilities([
            'carrier'      => $carrier->carrier,
            'recipient'    => $this->capabilitiesRecipient(),
            'package_type' => $v2PackageType,
        ]);
        $capability = $capabilities[0] ?? null;

        // SDK declares getDeliveryTypes() as non-nullable enum array, but at runtime returns
        // string constants and may be null when the API omits the field — coalesce defensively.
        $supportedV2Types = $capability ? ($capability->getDeliveryTypes() ?? []) : []; // @phpstan-ignore-line

        $currentV2DeliveryType = DeliveryOptions::DELIVERY_TYPES_V2_MAP[$deliveryOptions->deliveryType] ?? null;

        if ($currentV2DeliveryType && in_array($currentV2DeliveryType, $supportedV2Types, true)) { // @phpstan-ignore-line SDK enum type vs runtime string
            return;
        }

        $deliveryOptions->deliveryType = self::pickFallbackDeliveryType($supportedV2Types);
    }

    /**
     * Pick the safest available delivery type. Prefers STANDARD (matches the old
     * carrier-specific calculators' "when in doubt, standard" reset). Falls back to
     * the first listed type that maps to a known PDK name, then to the PDK default
     * when the carrier capability is missing entirely.
     */
    private static function pickFallbackDeliveryType(array $supportedV2Types): string
    {
        $v2ToPdkName = array_flip(DeliveryOptions::DELIVERY_TYPES_V2_MAP);

        if (in_array(RefTypesDeliveryTypeV2::STANDARD, $supportedV2Types, true)) {
            return $v2ToPdkName[RefTypesDeliveryTypeV2::STANDARD];
        }

        $firstKnown = Arr::first(
            $supportedV2Types,
            static function ($v2Type) use ($v2ToPdkName) {
                return isset($v2ToPdkName[$v2Type]);
            }
        );

        return $firstKnown ? $v2ToPdkName[$firstKnown] : DeliveryOptions::DEFAULT_DELIVERY_TYPE_NAME;
    }
}

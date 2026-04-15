<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

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
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct(PdkOrder $order)
    {
        parent::__construct($order);

        $this->capabilitiesService = Pdk::get(CapabilitiesValidationService::class);
        $this->countryService      = Pdk::get(CountryServiceInterface::class);
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

        if (! $carrier || ! $cc || ! $v2CurrentType) {
            return;
        }

        $capabilities = $this->capabilitiesService->getCapabilitiesForPackageType($cc, $v2CurrentType);
        $capability   = $capabilities[$carrier->carrier] ?? null;

        // International mailbox: capabilities determine availability, but the merchant must also enable it.
        if ($this->isInternationalMailboxBlocked($currentType, $cc, $carrier)) {
            $capability = null;
        }

        if ($capability) {
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
     * Fall back to the next available package type based on capabilities weight ordering.
     *
     * @param  string                              $cc
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return void
     */
    private function fallbackToNextAvailableType(string $cc, Carrier $carrier): void
    {
        $allV2Types   = DeliveryOptions::PACKAGE_TYPES_V2_MAP;
        $typeWeights  = $this->capabilitiesService->getPackageTypeWeights($cc, $allV2Types);

        // Filter to types that have capabilities for this carrier and are not blocked.
        $availableTypes = [];

        foreach ($allV2Types as $pdkName => $v2Name) {
            if ($this->isInternationalMailboxBlocked($pdkName, $cc, $carrier)) {
                continue;
            }

            $capabilities = $this->capabilitiesService->getCapabilitiesForPackageType($cc, $v2Name);

            if (isset($capabilities[$carrier->carrier])) {
                $availableTypes[] = $pdkName;
            }
        }

        // Sort available types by weight (from capabilities data) ascending.
        usort($availableTypes, static function (string $a, string $b) use ($typeWeights): int {
            $weightA = $typeWeights[$a] ?? null;
            $weightB = $typeWeights[$b] ?? null;

            return Utils::compareNullableInts($weightA, $weightB);
        });

        // Pick the first available type, or fall back to the default.
        $this->order->deliveryOptions->packageType = ! empty($availableTypes)
            ? $availableTypes[0]
            : DeliveryOptions::DEFAULT_PACKAGE_TYPE_NAME;
    }
}

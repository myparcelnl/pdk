<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * Centralized calculator for all tracked shipment option logic.
 */
final class TrackedCalculator extends AbstractPdkOrderOptionCalculator
{
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

        $this->countryService = Pdk::get(CountryServiceInterface::class);
    }

    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;
        $shipmentOptions = $deliveryOptions->shipmentOptions;

        // 1. DHL For You to non-local countries: tracked is disabled
        if ($this->isDhlForYouToNonLocal()) {
            $shipmentOptions->tracked = TriStateService::DISABLED;
            return;
        }

        // 2. Package small to non-NL: tracked is mandatory
        if ($this->isPackageSmallToNonNl()) {
            $shipmentOptions->tracked = TriStateService::ENABLED;
            return;
        }

        // 3. International mailbox without custom contract: tracked is mandatory
        if ($this->isInternationalMailboxWithoutContract()) {
            $shipmentOptions->tracked = TriStateService::ENABLED;
            return;
        }

        // 4. For all other cases: respect CarrierSettings (already set by TriStateOptionCalculator)
        // Do nothing - let the CarrierSettings value stand
    }

    /**
     * @return bool
     */
    private function isDhlForYouToNonLocal(): bool
    {
        $carrier     = $this->order->deliveryOptions->carrier;
        $isDhlForYou = Carrier::CARRIER_DHL_FOR_YOU_NAME === $carrier->name;
        $isNotLocal  = ! $this->countryService->isLocalCountry($this->order->shippingAddress->cc);

        return $isDhlForYou && $isNotLocal;
    }

    /**
     * @return bool
     */
    private function isInternationalMailboxWithoutContract(): bool
    {
        $isMailbox         = DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME === $this->order->deliveryOptions->packageType;
        $isNotLocal        = ! $this->countryService->isLocalCountry($this->order->shippingAddress->cc);
        $hasCustomContract = AccountSettings::hasCarrierSmallPackageContract();

        return $isMailbox && $isNotLocal && ! $hasCustomContract;
    }

    /**
     * @return bool
     */
    private function isPackageSmallToNonNl(): bool
    {
        $isPackageSmall = DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME === $this->order->deliveryOptions->packageType;
        $isNotNl        = CountryCodes::CC_NL !== $this->order->shippingAddress->cc;

        return $isPackageSmall && $isNotNl;
    }
}

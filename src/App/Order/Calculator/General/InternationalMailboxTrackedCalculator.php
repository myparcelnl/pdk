<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * Enforces tracked shipping for international mailbox when no custom contract is available.
 */
final class InternationalMailboxTrackedCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @var \MyParcelNL\Pdk\Base\Contract\CountryServiceInterface
     */
    private $countryService;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     */
    public function __construct($order)
    {
        parent::__construct($order);

        $this->countryService = Pdk::get(CountryServiceInterface::class);
    }

    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;
        $shipmentOptions = $deliveryOptions->shipmentOptions;

        $isMailbox         = DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME === $deliveryOptions->packageType;
        $isNotLocal        = ! $this->countryService->isLocalCountry($this->order->shippingAddress->cc);
        $hasCustomContract = AccountSettings::hasCarrierSmallPackageContract();

        // For non-custom contract international mailbox: tracked is mandatory
        if ($isMailbox && $isNotLocal && ! $hasCustomContract) {
            $shipmentOptions->tracked = TriStateService::ENABLED;
        }
    }
}

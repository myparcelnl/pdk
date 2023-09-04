<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\DhlForYou;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * - Evening delivery is only allowed in the Netherlands.
 * - When evening delivery is enabled same-day delivery is not available
 */
final class DhlForYouDeliveryTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        if (CountryCodes::CC_NL !== $this->order->shippingAddress->cc) {
            $this->order->deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME;
            return;
        }

        if (DeliveryOptions::DELIVERY_TYPE_EVENING_NAME !== $this->order->deliveryOptions->deliveryType) {
            return;
        }

        $shipmentOptions->sameDayDelivery = TriStateService::DISABLED;
    }
}

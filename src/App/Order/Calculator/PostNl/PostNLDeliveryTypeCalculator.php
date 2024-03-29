<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\PostNl;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

/**
 * Morning and evening delivery are only available in the Netherlands.
 * When morning or evening delivery is enabled, signature and only recipient are required, but age check is not allowed.
 */
final class PostNLDeliveryTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;

        if (CountryCodes::CC_NL !== $this->order->shippingAddress->cc) {
            $deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME;

            return;
        }

        switch ($this->order->deliveryOptions->deliveryType) {
            case DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME:
                $deliveryOptions->shipmentOptions->signature     = TriStateService::ENABLED;
                $deliveryOptions->shipmentOptions->onlyRecipient = TriStateService::DISABLED;
                $deliveryOptions->shipmentOptions->return        = TriStateService::DISABLED;
                break;

            case DeliveryOptions::DELIVERY_TYPE_MORNING_NAME:
            case DeliveryOptions::DELIVERY_TYPE_EVENING_NAME:
                $shipmentOptions = $deliveryOptions->shipmentOptions;

                $shipmentOptions->ageCheck      = TriStateService::DISABLED;
                $shipmentOptions->onlyRecipient = TriStateService::ENABLED;
                $shipmentOptions->signature     = TriStateService::ENABLED;
                break;
        }
    }
}

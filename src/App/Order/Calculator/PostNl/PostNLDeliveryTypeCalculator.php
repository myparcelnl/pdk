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

        if (! $this->isMorningOrEveningDelivery()) {
            return;
        }

        $shipmentOptions = $deliveryOptions->shipmentOptions;

        $shipmentOptions->ageCheck      = TriStateService::DISABLED;
        $shipmentOptions->onlyRecipient = TriStateService::ENABLED;
        $shipmentOptions->signature     = TriStateService::ENABLED;
    }

    /**
     * @return bool
     */
    private function isMorningOrEveningDelivery(): bool
    {
        return in_array(
            $this->order->deliveryOptions->deliveryType,
            [DeliveryOptions::DELIVERY_TYPE_MORNING_NAME, DeliveryOptions::DELIVERY_TYPE_EVENING_NAME],
            true
        );
    }
}

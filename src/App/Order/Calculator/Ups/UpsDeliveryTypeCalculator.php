<?php

namespace MyParcelNL\Pdk\App\Order\Calculator\Ups;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

class UpsDeliveryTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    //todo: increase coverage
    /**
     * @inheritDoc
     */
    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;
        $cc              = $this->order->shippingAddress->cc;

        switch ($deliveryOptions->deliveryType) {
            case DeliveryOptions::DELIVERY_TYPE_EXPRESS_NAME:
                if ($cc !== CountryCodes::CC_NL) {
                    $deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME;
                }
        }
    }
}

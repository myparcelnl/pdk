<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPS;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

class UPSDeliveryTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @inheritDoc
     */
    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;

        $isExpress = $deliveryOptions->deliveryType === DeliveryOptions::DELIVERY_TYPE_EXPRESS_NAME;
        if (
            $isExpress
            && $this->order->shippingAddress->cc !== CountryCodes::CC_NL
        ) {
            $deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME;
        }
    }
}

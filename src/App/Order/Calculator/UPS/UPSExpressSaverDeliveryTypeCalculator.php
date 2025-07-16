<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPS;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

class UPSExpressSaverDeliveryTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    private const EXPRESS_SUPPORTED_COUNTRIES = [
        CountryCodes::CC_NL,
        CountryCodes::CC_BE,
    ];

    /**
     * @inheritDoc
     */
    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;

        $isExpress = $deliveryOptions->deliveryType === DeliveryOptions::DELIVERY_TYPE_EXPRESS_NAME;
        if (
            $isExpress
            && ! in_array($this->order->shippingAddress->cc, self::EXPRESS_SUPPORTED_COUNTRIES, true)
        ) {
            $deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME;
        }
    }
}

<?php

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

final class PackageTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        if (DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME === $this->order->deliveryOptions->packageType) {
            return;
        }
        if (CountryCodes::CC_NL !== $this->order->shippingAddress->cc) {
            $this->order->deliveryOptions->packageType = DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME;
        }
    }
}

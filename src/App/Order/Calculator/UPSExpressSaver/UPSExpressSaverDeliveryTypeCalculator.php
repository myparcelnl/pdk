<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPSExpressSaver;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

final class UPSExpressSaverDeliveryTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @inheritDoc
     */
    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;

        // UPS Express Saver must always have express delivery type
        if ($deliveryOptions->deliveryType !== DeliveryOptions::DELIVERY_TYPE_EXPRESS_NAME) {
            $deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_EXPRESS_NAME;
        }
    }
}

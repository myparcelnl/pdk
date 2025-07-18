<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPSStandard;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;

final class UPSStandardDeliveryTypeCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * @inheritDoc
     */
    public function calculate(): void
    {
        $deliveryOptions = $this->order->deliveryOptions;

        // UPS Standard must always have standard delivery type
        if ($deliveryOptions->deliveryType !== DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME) {
            $deliveryOptions->deliveryType = DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME;
        }
    }
}

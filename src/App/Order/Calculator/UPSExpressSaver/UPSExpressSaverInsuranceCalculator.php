<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPSExpressSaver;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;

/**
 * Set default insurance to 10000 (100 euro) for UPS Express Saver shipments
 */
final class UPSExpressSaverInsuranceCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        // Set default insurance to 10000 (100 euro) if not set
        if (!isset($shipmentOptions->insurance) || $shipmentOptions->insurance === 0) {
            $shipmentOptions->insurance = 10000;
        }
    }
} 
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPS;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;

class UPSCountryShipmentOptionsCalculator extends AbstractPdkOrderOptionCalculator
{
    // Comment: Always set delivery date to null for UPS shipments regardless of country
    public function calculate(): void
    {
        $this->order->deliveryOptions->date = null;
    }
}

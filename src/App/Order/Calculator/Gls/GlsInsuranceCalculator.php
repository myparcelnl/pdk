<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Gls;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;

/**
 * Set insurance to 10000 (100 euro) for GLS shipments
 * GLS has no choice in insurance - 100 euro is standard and included in base tariff
 */
final class GlsInsuranceCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $shipmentOptions = $this->order->deliveryOptions->shipmentOptions;

        // GLS always has 10000 (100 euro) insurance - no other choice available
        $shipmentOptions->insurance = 10000;
    }
}

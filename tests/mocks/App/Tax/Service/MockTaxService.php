<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Tax\Service;

final class MockTaxService extends AbstractTaxService
{
    public function getShippingDisplayPrice(float $basePrice): float
    {
        return 0.21 * $basePrice;
    }
}

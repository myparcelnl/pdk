<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Gls;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;

final class GlsCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            GlsShipmentOptionsCalculator::class,
            GlsInsuranceCalculator::class,
            GlsWeightCalculator::class,
        ];
    }

    protected function getCarrier(): string
    {
        return RefCapabilitiesSharedCarrierV2::GLS;
    }
}

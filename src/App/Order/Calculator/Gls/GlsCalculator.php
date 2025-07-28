<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Gls;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

final class GlsCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            GlsShipmentOptionsCalculator::class,
        ];
    }

    protected function getCarrier(): string
    {
        return Carrier::CARRIER_GLS_NAME;
    }
}

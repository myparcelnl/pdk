<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPS;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;

final class UPSExpressSaverCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            UPSCountryShipmentOptionsCalculator::class,
            UPSAgeCheckCalculator::class,
            UPSExpressSaverDeliveryTypeCalculator::class,
        ];
    }
} 
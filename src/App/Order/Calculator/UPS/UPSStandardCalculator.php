<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPS;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;

final class UPSStandardCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            UPSCountryShipmentOptionsCalculator::class,
            UPSAgeCheckCalculator::class,
        ];
    }
}

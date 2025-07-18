<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPSExpressSaver;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;

final class UPSExpressSaverCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            UPSExpressSaverCountryShipmentOptionsCalculator::class,
            UPSExpressSaverAgeCheckCalculator::class,
            UPSExpressSaverDeliveryTypeCalculator::class,
            UPSExpressSaverInsuranceCalculator::class,
        ];
    }
}

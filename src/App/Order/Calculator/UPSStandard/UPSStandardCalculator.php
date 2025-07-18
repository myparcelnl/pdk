<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\UPSStandard;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;

final class UPSStandardCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            UPSStandardCountryShipmentOptionsCalculator::class,
            UPSStandardAgeCheckCalculator::class,
            UPSStandardDeliveryTypeCalculator::class,
            UPSStandardInsuranceCalculator::class,
        ];
    }
}

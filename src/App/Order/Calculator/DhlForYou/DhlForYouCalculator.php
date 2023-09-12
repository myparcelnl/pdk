<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\DhlForYou;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

final class DhlForYouCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            DhlForYouCountryShipmentOptionsCalculator::class,
            DhlForYouDeliveryTypeCalculator::class,
            DhlForYouShipmentOptionsCalculator::class,
        ];
    }

    protected function getCarrier(): string
    {
        return Carrier::CARRIER_DHL_FOR_YOU_NAME;
    }
}

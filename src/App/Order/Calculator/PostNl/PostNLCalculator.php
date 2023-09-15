<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\PostNl;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;
use MyParcelNL\Pdk\Carrier\Model\Carrier;

final class PostNLCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            PostNLCountryShipmentOptionsCalculator::class,
            PostNLAgeCheckCalculator::class,
            PostNLDeliveryTypeCalculator::class,
        ];
    }

    protected function getCarrier(): string
    {
        return Carrier::CARRIER_POSTNL_NAME;
    }
}

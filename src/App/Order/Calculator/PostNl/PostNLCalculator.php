<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\PostNl;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;

final class PostNLCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            PostNLCountryShipmentOptionsCalculator::class,
            PostNLAgeCheckCalculator::class,
            PostNLReceiptCodeCalculator::class,
            PostNLDeliveryTypeCalculator::class,
        ];
    }
}

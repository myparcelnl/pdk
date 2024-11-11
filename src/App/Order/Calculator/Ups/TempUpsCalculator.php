<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Ups;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;

final class TempUpsCalculator extends AbstractCarrierOptionsCalculator
{
    //todo: increase coverage
    protected function getCalculators(): array
    {
        return [
            UpsAgeCheckCalculator::class,
            UpsDeliveryTypeCalculator::class,
        ];
    }
}

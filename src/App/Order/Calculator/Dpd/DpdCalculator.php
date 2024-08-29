<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Dpd;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;

final class DpdCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            DpdDeliveryDateCalculator::class,
        ];
    }
}

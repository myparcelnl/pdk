<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Bpost;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractCarrierOptionsCalculator;

final class BpostCalculator extends AbstractCarrierOptionsCalculator
{
    protected function getCalculators(): array
    {
        return [
            BpostDeliveryDateCalculator::class,
        ];
    }
}

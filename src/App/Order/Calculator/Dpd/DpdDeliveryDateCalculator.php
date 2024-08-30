<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Dpd;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;

final class DpdDeliveryDateCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $this->order->deliveryOptions->date = null;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Bpost;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;

final class BpostDeliveryDateCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $this->order->deliveryOptions->date = null;
    }
}

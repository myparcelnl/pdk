<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\Bpost;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;

final class BpostDeliveryDateCalculator extends AbstractPdkOrderOptionCalculator
{
    /**
     * Sets the delivery date to null as Bpost carrier does not accept delivery dates.
     */
    public function calculate(): void
    {
        $this->order->deliveryOptions->date = null;
    }
}

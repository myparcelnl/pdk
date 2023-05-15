<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Contract;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

interface ShipmentOptionsServiceInterface
{
    /**
     * Calculate shipment options for an order based on default settings, order lines and the order itself.
     */
    public function calculate(PdkOrder $order): void;
}

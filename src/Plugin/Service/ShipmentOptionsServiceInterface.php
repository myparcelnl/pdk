<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Service;

use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

interface ShipmentOptionsServiceInterface
{
    /**
     * Calculate shipment options for an order based on default settings, order lines and the order itself.
     */
    public function calculate(PdkOrder $order): void;
}

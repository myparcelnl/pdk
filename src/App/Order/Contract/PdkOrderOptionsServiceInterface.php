<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

interface PdkOrderOptionsServiceInterface
{
    public const EXCLUDE_SHIPMENT_OPTIONS = 1;
    public const EXCLUDE_PRODUCT_SETTINGS = 2;
    public const EXCLUDE_CARRIER_SETTINGS = 4;

    public function calculate(PdkOrder $order): PdkOrder;

    public function calculateShipmentOptions(PdkOrder $order, int $flags = 0): PdkOrder;
}

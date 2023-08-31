<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

interface PdkOrderOptionsServiceInterface
{
    public const EXCLUDE_SHIPMENT_OPTIONS = 1;
    public const EXCLUDE_PRODUCT_SETTINGS = 2;
    public const EXCLUDE_CARRIER_SETTINGS = 4;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function calculate(PdkOrder $order): PdkOrder;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     * @param  int                                      $flags
     *
     * @return \MyParcelNL\Pdk\App\Order\Model\PdkOrder
     */
    public function calculateShipmentOptions(PdkOrder $order, int $flags = 0): PdkOrder;
}

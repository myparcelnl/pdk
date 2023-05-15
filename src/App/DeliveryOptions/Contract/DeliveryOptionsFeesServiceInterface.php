<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\DeliveryOptions\Contract;

use MyParcelNL\Pdk\App\Cart\Collection\PdkCartFeeCollection;
use MyParcelNL\Pdk\App\Cart\Model\PdkCartFee;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

interface DeliveryOptionsFeesServiceInterface
{
    public function createFee(string $identifier, DeliveryOptions $deliveryOptions): PdkCartFee;

    public function getFees(DeliveryOptions $deliveryOptions): PdkCartFeeCollection;
}

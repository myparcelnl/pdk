<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Contract;

use MyParcelNL\Pdk\Plugin\Collection\PdkCartFeeCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkCartFee;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

interface DeliveryOptionsFeesServiceInterface
{
    public function createFee(string $identifier, DeliveryOptions $deliveryOptions): PdkCartFee;

    public function getFees(DeliveryOptions $deliveryOptions): PdkCartFeeCollection;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryType;

/**
 * @property \MyParcelNL\Pdk\Shipment\Model\DeliveryType[] $items
 */
class DeliveryTypeCollection extends Collection
{
    protected $cast = DeliveryType::class;
}

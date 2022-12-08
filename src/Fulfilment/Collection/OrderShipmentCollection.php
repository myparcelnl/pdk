<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Model\OrderShipment;

/**
 * @property \MyParcelNL\Pdk\Fulfilment\Model\OrderShipment[] $items
 */
class OrderShipmentCollection extends Collection
{
    protected $cast = OrderShipment::class;
}

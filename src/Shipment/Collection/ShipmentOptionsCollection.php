<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;

/**
 * @property \MyParcelNL\Pdk\Shipment\Model\ShipmentOptions[] $items
 */
class ShipmentOptionsCollection extends Collection
{
    protected $cast = ShipmentOptions::class;
}

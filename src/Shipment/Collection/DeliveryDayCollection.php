<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryDay;

/**
 * @property \MyParcelNL\Pdk\Shipment\Model\DeliveryDay[] $items
 */
class DeliveryDayCollection extends Collection
{
    protected $cast = DeliveryDay::class;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Shipment\Model\DropOffDay;

/**
 * @property \MyParcelNL\Pdk\Shipment\Model\DropOffDay[] $items
 */
class DropOffDayCollection extends Collection
{
    protected $cast = DropOffDay::class;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection;

/**
 * @property null|DropOffDayCollection $dropOffDays
 * @property null|DropOffDayCollection $dropOffDaysException
 * @property null|int                  $dropOffDelay
 * @property null|int                  $deliveryDaysWindow
 */
class DropOffDayPossibilities extends Model
{
    protected $attributes = [
        'dropOffDays'          => null,
        'dropOffDaysException' => null,
        'dropOffDelay'         => null,
        'deliveryDaysWindow'   => null,
    ];

    protected $casts      = [
        'dropOffDays'          => DropOffDayCollection::class,
        'dropOffDaysException' => DropOffDayCollection::class,
        'dropOffDelay'         => 'int',
        'deliveryDaysWindow'   => 'int',
    ];
}

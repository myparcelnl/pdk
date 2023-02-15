<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Shipment\Collection\DropOffDayCollection;

/**
 * @property DropOffDayCollection $dropOffDays
 * @property DropOffDayCollection $dropOffDaysDeviations
 */
class DropOffPossibilities extends Model
{
    protected $attributes = [
        'dropOffDays'           => DropOffDayCollection::class,
        'dropOffDaysDeviations' => DropOffDayCollection::class,
    ];

    protected $casts      = [
        'dropOffDays'           => DropOffDayCollection::class,
        'dropOffDaysDeviations' => DropOffDayCollection::class,
    ];
}

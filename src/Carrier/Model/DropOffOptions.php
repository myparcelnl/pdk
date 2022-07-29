<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCapabilitiesCollection;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Shipment\Collection\DefaultLogger;
use MyParcelNL\Pdk\Shipment\Collection\DeliveryDayCollection;
use MyParcelNL\Sdk\src\Support\Arr;

/**
 * @property null|DeliveryDayCollection $dropOffDays
 * @property null|DeliveryDayCollection $dropOffDaysException
 * @property null|int                   $dropOffDelay
 * @property null|int                   $deliveryDaysWindow
 */
class DropOffOptions extends Model
{
    protected $attributes = [
        'dropOffDays'          => null,
        'dropOffDaysException' => null,
        'dropOffDelay'         => null,
        'deliveryDaysWindow'   => null,
    ];

    protected $casts      = [
        'dropOffDays'          => DeliveryDayCollection::class,
        'dropOffDaysException' => DeliveryDayCollection::class,
        'dropOffDelay'         => 'int',
        'deliveryDaysWindow'   => 'int',
    ];
}

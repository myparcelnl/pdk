<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|\DateTime                                             $date
 * @property null|string                                                $cutoffTime
 * @property null|boolean                                               $dispatch
 * @property null|string                                                $sameDayCutoffTime
 */
class DropOffDay extends Model
{
    protected $attributes = [
        'date'              => null,
        'cutoffTime'        => null,
        'sameDayCutoffTime' => null,
        'dispatch'          => true,
    ];

    protected $casts      = [
        'date'              => DateTime::class,
        'cutoffTime'        => 'string',
        'sameDayCutoffTime' => 'string',
        'dispatch'          => 'boolean',
    ];
}

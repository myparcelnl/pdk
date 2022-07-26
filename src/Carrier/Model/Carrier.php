<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Collection\CarrierOptionsCollection;

class Carrier extends Model
{
    protected $attributes = [
        'id'               => null,
        'name'             => null,
        'human'            => null,
        'subscriptionId'   => null,
        'primary'          => null,
        'type'             => null,
        'recipientOptions' => CarrierOptionsCollection::class,
        'returnOptions'    => CarrierOptionsCollection::class,
    ];

    protected $casts      = [
        'id'               => 'int',
        'name'             => 'string',
        'human'            => 'string',
        'subscriptionId'   => 'int',
        'primary'          => 'bool',
        'type'             => 'string',
        'recipientOptions' => CarrierOptionsCollection::class,
        'returnOptions'    => CarrierOptionsCollection::class,
    ];
}

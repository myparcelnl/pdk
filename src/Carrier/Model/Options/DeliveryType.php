<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model\Options;

use MyParcelNL\Pdk\Base\Model\Model;

class DeliveryType extends Model
{
    protected $attributes = [
        'id'   => null,
        'name' => null,
    ];

    protected $casts      = [
        'id'   => 'int',
        'name' => 'string',
    ];
}

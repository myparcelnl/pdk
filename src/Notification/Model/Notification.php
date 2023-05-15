<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Model;

use MyParcelNL\Pdk\Base\Model\Model;

class Notification extends Model
{
    protected $attributes = [
        'title'    => null,
        'category' => 'api',
        'content'  => null,
        'timeout'  => false,
        'variant'  => 'info',
    ];

    protected $cast       = [
        'title'    => 'string',
        'category' => 'string',
        'content'  => 'string',
        'timeout'  => 'bool',
        'variant'  => 'string',
    ];
}

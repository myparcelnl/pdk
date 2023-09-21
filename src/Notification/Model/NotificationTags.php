<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Model;

use MyParcelNL\Pdk\Base\Model\Model;

class NotificationTags extends Model
{
    protected $attributes = [
        'action'   => null,
        'orderIds' => null,
    ];

    protected $casts      = [
        'action'   => 'string',
        'orderIds' => 'string',
    ];
}

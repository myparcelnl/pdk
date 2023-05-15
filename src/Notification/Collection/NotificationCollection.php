<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Notification\Model\Notification;

class NotificationCollection extends Collection
{
    protected $cast = Notification::class;
}

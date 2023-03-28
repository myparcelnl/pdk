<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Notification\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Plugin\Notification\Model\Notification;

class NotificationCollection extends Collection
{
    protected $cast = Notification::class;
}

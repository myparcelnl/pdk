<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Collection;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Notification\Model\Notification;

/**
 * @property \MyParcelNL\Pdk\Notification\Model\Notification[] $items
 */
class NotificationCollection extends Collection
{
    protected $cast = Notification::class;
}

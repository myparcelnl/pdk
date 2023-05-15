<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Notification\NotificationManager;

/**
 * @method static void add(string $title, $content, string $level = 'info')
 * @method static void addMany(array $notifications)
 * @method static mixed all()
 * @method static bool isNotEmpty()
 * @method static bool isEmpty()
 * @implements \MyParcelNL\Pdk\Notification\NotificationManager
 */
final class Notifications extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NotificationManager::class;
    }
}

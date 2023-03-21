<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Plugin\NotificationManager;

/**
 * @method static void add(string $message, string $level = 'info')
 * @method static void addMany(array $notifications)
 * @method static mixed get()
 * @method static bool has()
 * @implements \MyParcelNL\Pdk\Plugin\NotificationManager
 */
class Notifications extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NotificationManager::class;
    }
}

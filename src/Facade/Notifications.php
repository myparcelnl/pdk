<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Notification\Contract\NotificationServiceInterface;

/**
 * @method static void add(string $title, string|string[] $content, string $level = 'info')
 * @method static void error(string $title, string|string[] $content)
 * @method static void warning(string $title, string|string[] $content)
 * @method static void info(string $title, string|string[] $content)
 * @method static void success(string $title, string|string[] $content)
 * @method static Collection all()
 * @method static void clear()
 * @method static bool isEmpty()
 * @method static bool isNotEmpty()
 * @see \MyParcelNL\Pdk\Notification\Contract\NotificationServiceInterface
 */
final class Notifications extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NotificationServiceInterface::class;
    }
}

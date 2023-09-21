<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Notification\Contract\NotificationServiceInterface;
use MyParcelNL\Pdk\Notification\Model\NotificationTags as Tags;

/**
 * @method static void add(string $title, string|string[] $content, string $level, ?string $category, ?Tags $tags)
 * @method static void error(string $title, string|string[] $content, ?string $category, ?Tags $tags)
 * @method static void warning(string $title, string|string[] $content, ?string $category, ?Tags $tags)
 * @method static void info(string $title, string|string[] $content, ?string $category, ?Tags $tags)
 * @method static void success(string $title, string|string[] $content, ?string $category, ?Tags $tags)
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

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification;

use MyParcelNL\Pdk\Notification\Collection\NotificationCollection;
use MyParcelNL\Pdk\Notification\Contract\NotificationManagerInterface;

class NotificationManager implements NotificationManagerInterface
{
    public const LEVEL_INFO = 'info';

    /**
     * @var array
     */
    private $notifications;

    public function __construct()
    {
        $this->notifications = new NotificationCollection();
    }

    /**
     * @param  null|string          $title
     * @param  null|string[]|string $content
     * @param  string               $variant
     *
     * @return void
     */
    public function add(?string $title, $content, string $variant = self::LEVEL_INFO): void
    {
        $this->notifications->push([
            'content' => $content,
            'title'   => $title,
            'variant' => $variant,
        ]);
    }

    /**
     * @return \MyParcelNL\Pdk\Notification\Collection\NotificationCollection
     */
    public function all(): NotificationCollection
    {
        return $this->notifications;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->notifications->isEmpty();
    }

    /**
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return $this->notifications->isNotEmpty();
    }
}

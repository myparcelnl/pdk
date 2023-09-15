<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Service;

use MyParcelNL\Pdk\Notification\Collection\NotificationCollection;
use MyParcelNL\Pdk\Notification\Contract\NotificationServiceInterface;
use MyParcelNL\Pdk\Notification\Model\Notification;

class NotificationService implements NotificationServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\Notification\Collection\NotificationCollection
     */
    protected $notifications;

    public function __construct()
    {
        $this->clear();
    }

    /**
     * @param  null|string    $title
     * @param  null|string[]| $content
     */
    public function add(?string $title, $content, string $variant): void
    {
        $this->notifications->push([
            'content' => $content,
            'title'   => $title,
            'variant' => $variant,
        ]);
    }

    public function all(): NotificationCollection
    {
        return $this->notifications;
    }

    public function clear(): void
    {
        $this->notifications = new NotificationCollection();
    }

    /**
     * @param  string|string[] $content
     */
    public function error(string $title, $content): void
    {
        $this->add($title, $content, Notification::VARIANT_ERROR);
    }

    /**
     * @param  string|string[] $content
     */
    public function info(string $title, $content): void
    {
        $this->add($title, $content, Notification::VARIANT_INFO);
    }

    public function isEmpty(): bool
    {
        return $this->notifications->isEmpty();
    }

    public function isNotEmpty(): bool
    {
        return $this->notifications->isNotEmpty();
    }

    /**
     * @param  string|string[] $content
     */
    public function success(string $title, $content): void
    {
        $this->add($title, $content, Notification::VARIANT_SUCCESS);
    }

    /**
     * @param  string|string[] $content
     */
    public function warning(string $title, $content): void
    {
        $this->add($title, $content, Notification::VARIANT_WARNING);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Contract;

use MyParcelNL\Pdk\Notification\Collection\NotificationCollection;

interface NotificationServiceInterface
{
    /**
     * @param  null|string     $title
     * @param  string|string[] $content
     */
    public function add(?string $title, $content, string $variant): void;

    public function all(): NotificationCollection;

    /**
     * @param  string|string[] $content
     */
    public function error(string $title, $content): void;

    /**
     * @param  string|string[] $content
     */
    public function info(string $title, $content): void;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;

    /**
     * @param  string|string[] $content
     */
    public function success(string $title, $content): void;

    /**
     * @param  string|string[] $content
     */
    public function warning(string $title, $content): void;
}

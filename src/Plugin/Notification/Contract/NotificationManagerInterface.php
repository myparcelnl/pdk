<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Notification\Contract;

use MyParcelNL\Pdk\Plugin\Notification\Collection\NotificationCollection;

interface NotificationManagerInterface
{
    /**
     * @param  null|string $title
     * @param              $content
     * @param  string      $variant
     *
     * @return void
     */
    public function add(?string $title, $content, string $variant): void;

    public function all(): NotificationCollection;

    public function isEmpty(): bool;

    public function isNotEmpty(): bool;
}

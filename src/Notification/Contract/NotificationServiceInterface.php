<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Contract;

use MyParcelNL\Pdk\Notification\Collection\NotificationCollection;

interface NotificationServiceInterface
{
    /**
     * @param  null|string     $title
     * @param  string|string[] $content
     * @param  string          $variant
     *
     * @return void
     */
    public function add(?string $title, $content, string $variant): void;

    /**
     * @return \MyParcelNL\Pdk\Notification\Collection\NotificationCollection
     */
    public function all(): NotificationCollection;

    /**
     * @param  string          $title
     * @param  string|string[] $content
     *
     * @return void
     */
    public function error(string $title, $content): void;

    /**
     * @param  string          $title
     * @param  string|string[] $content
     *
     * @return void
     */
    public function info(string $title, $content): void;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return bool
     */
    public function isNotEmpty(): bool;

    /**
     * @param  string          $title
     * @param  string|string[] $content
     *
     * @return void
     */
    public function success(string $title, $content): void;

    /**
     * @param  string          $title
     * @param  string|string[] $content
     *
     * @return void
     */
    public function warning(string $title, $content): void;
}

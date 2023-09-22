<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Contract;

use MyParcelNL\Pdk\Notification\Collection\NotificationCollection;
use MyParcelNL\Pdk\Notification\Model\Notification;

interface NotificationServiceInterface
{
    /**
     * @param  null|string     $title
     * @param  string|string[] $content
     * @param  string          $variant
     * @param  null|string     $category
     * @param  array           $tags
     *
     * @return void
     */
    public function add(
        ?string $title,
                $content,
        string  $variant,
        ?string $category = Notification::DEFAULT_CATEGORY,
        array   $tags = []
    ): void;

    /**
     * @return \MyParcelNL\Pdk\Notification\Collection\NotificationCollection
     */
    public function all(): NotificationCollection;

    /**
     * @param  string          $title
     * @param  string|string[] $content
     * @param  null|string     $category
     * @param  array           $tags
     *
     * @return void
     */
    public function error(
        string  $title,
                $content,
        ?string $category = Notification::DEFAULT_CATEGORY,
        array   $tags = []
    ): void;

    /**
     * @param  string          $title
     * @param  string|string[] $content
     * @param  null|string     $category
     * @param  array           $tags
     *
     * @return void
     */
    public function info(
        string  $title,
                $content,
        ?string $category = Notification::DEFAULT_CATEGORY,
        array   $tags = []
    ): void;

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
     * @param  null|string     $category
     * @param  array           $tags
     *
     * @return void
     */
    public function success(
        string  $title,
                $content,
        ?string $category = Notification::DEFAULT_CATEGORY,
        array   $tags = []
    ): void;

    /**
     * @param  string          $title
     * @param  string|string[] $content
     * @param  null|string     $category
     * @param  array           $tags
     *
     * @return void
     */
    public function warning(
        string  $title,
                $content,
        ?string $category = Notification::DEFAULT_CATEGORY,
        array   $tags = []
    ): void;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Contract;

use MyParcelNL\Pdk\Notification\Collection\NotificationCollection;
use MyParcelNL\Pdk\Notification\Model\NotificationTags;

interface NotificationServiceInterface
{
    /**
     * @param  null|string                                              $title
     * @param  string|string[]                                          $content
     * @param  string                                                   $variant
     * @param  null|string                                              $category
     * @param  null|\MyParcelNL\Pdk\Notification\Model\NotificationTags $tags
     *
     * @return void
     */
    public function add(?string $title, $content, string $variant, ?string $category, ?NotificationTags $tags): void;

    /**
     * @return \MyParcelNL\Pdk\Notification\Collection\NotificationCollection
     */
    public function all(): NotificationCollection;

    /**
     * @param  string                                                   $title
     * @param  string|string[]                                          $content
     * @param  null|string                                              $category
     * @param  null|\MyParcelNL\Pdk\Notification\Model\NotificationTags $tags
     *
     * @return void
     */
    public function error(string $title, $content, ?string $category, ?NotificationTags $tags): void;

    /**
     * @param  string                                                   $title
     * @param  string|string[]                                          $content
     * @param  null|string                                              $category
     * @param  null|\MyParcelNL\Pdk\Notification\Model\NotificationTags $tags
     *
     * @return void
     */
    public function info(string $title, $content, ?string $category, ?NotificationTags $tags): void;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return bool
     */
    public function isNotEmpty(): bool;

    /**
     * @param  string                                                   $title
     * @param  string|string[]                                          $content
     * @param  null|string                                              $category
     * @param  null|\MyParcelNL\Pdk\Notification\Model\NotificationTags $tags
     *
     * @return void
     */
    public function success(string $title, $content, ?string $category, ?NotificationTags $tags): void;

    /**
     * @param  string                                                   $title
     * @param  string|string[]                                          $content
     * @param  null|string                                              $category
     * @param  null|\MyParcelNL\Pdk\Notification\Model\NotificationTags $tags
     *
     * @return void
     */
    public function warning(string $title, $content, ?string $category, ?NotificationTags $tags): void;
}

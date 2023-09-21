<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Service;

use MyParcelNL\Pdk\Notification\Collection\NotificationCollection;
use MyParcelNL\Pdk\Notification\Contract\NotificationServiceInterface;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Notification\Model\NotificationTags;

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
     * @param  null|string           $title
     * @param  null|string[]         $content
     * @param  string                $variant
     * @param  null|string           $category
     * @param  null|NotificationTags $tags
     *
     * @return void
     */
    public function add(
        ?string           $title,
                          $content,
        string            $variant = Notification::DEFAULT_VARIANT,
        ?string           $category = Notification::DEFAULT_CATEGORY,
        ?NotificationTags $tags = null
    ): void {
        $this->notifications->push([
            'content'  => $content,
            'title'    => $title,
            'variant'  => $variant,
            'category' => $category ?? Notification::DEFAULT_CATEGORY,
            'tags'     => $tags,
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
     * @return void
     */
    public function clear(): void
    {
        $this->notifications = new NotificationCollection();
    }

    /**
     * @param  string                $title
     * @param  string|string[]       $content
     * @param  null|string           $category
     * @param  null|NotificationTags $tags
     *
     * @return void
     */
    public function error(
        string            $title,
                          $content,
        ?string           $category = Notification::DEFAULT_CATEGORY,
        ?NotificationTags $tags = null
    ): void {
        $this->add($title, $content, Notification::VARIANT_ERROR, $category, $tags);
    }

    /**
     * @param  string                $title
     * @param  string|string[]       $content
     * @param  null|string           $category
     * @param  null|NotificationTags $tags
     *
     * @return void
     */
    public function info(
        string            $title,
                          $content,
        ?string           $category = Notification::DEFAULT_CATEGORY,
        ?NotificationTags $tags = null
    ): void {
        $this->add($title, $content, Notification::VARIANT_INFO, $category, $tags);
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

    /**
     * @param  string                $title
     * @param  string|string[]       $content
     * @param  null|string           $category
     * @param  null|NotificationTags $tags
     *
     * @return void
     */
    public function success(
        string            $title,
                          $content,
        ?string           $category = Notification::DEFAULT_CATEGORY,
        ?NotificationTags $tags = null
    ): void {
        $this->add($title, $content, Notification::VARIANT_SUCCESS, $category, $tags);
    }

    /**
     * @param  string                $title
     * @param  string|string[]       $content
     * @param  null|string           $category
     * @param  null|NotificationTags $tags
     *
     * @return void
     */
    public function warning(
        string            $title,
                          $content,
        ?string           $category = Notification::DEFAULT_CATEGORY,
        ?NotificationTags $tags = null
    ): void {
        $this->add($title, $content, Notification::VARIANT_WARNING, $category, $tags);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string|null                                              $title
 * @property string|null                                              $content
 * @property string                                                   $category
 * @property \MyParcelNL\Pdk\Notification\Model\NotificationTags|null $tags
 * @property bool                                                     $timeout
 * @property string                                                   $variant
 */
class Notification extends Model
{
    public const VARIANT_INFO    = 'info';
    public const VARIANT_WARNING = 'warning';
    public const VARIANT_ERROR   = 'error';
    public const VARIANT_SUCCESS = 'success';
    public const DEFAULT_VARIANT = self::VARIANT_INFO;
    public const VARIANTS        = [
        self::VARIANT_INFO,
        self::VARIANT_WARNING,
        self::VARIANT_ERROR,
        self::VARIANT_SUCCESS,
    ];
    public const CATEGORY_ACTION  = 'action';
    public const CATEGORY_API     = 'api';
    public const CATEGORY_GENERAL = 'general';
    public const DEFAULT_CATEGORY = self::CATEGORY_API;
    public const CATEGORIES = [
        self::CATEGORY_ACTION,
        self::CATEGORY_API,
        self::CATEGORY_GENERAL,
    ];

    protected $attributes = [
        'title'    => null,
        'content'  => null,
        'category' => self::DEFAULT_CATEGORY,
        'tags'     => null,
        'timeout'  => false,
        'variant'  => self::DEFAULT_VARIANT,
    ];

    protected $cast       = [
        'title'    => 'string',
        'content'  => 'string',
        'category' => 'string',
        'tags'     => NotificationTags::class,
        'timeout'  => 'bool',
        'variant'  => 'string',
    ];
}

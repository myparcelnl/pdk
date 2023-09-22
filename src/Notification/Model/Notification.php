<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;

/**
 * @property string                $category
 * @property string[]              $content
 * @property array<string, scalar> $tags
 * @property bool                  $timeout
 * @property string|null           $title
 * @property string                $variant
 */
class Notification extends Model
{
    public const VARIANT_INFO     = 'info';
    public const VARIANT_WARNING  = 'warning';
    public const VARIANT_ERROR    = 'error';
    public const VARIANT_SUCCESS  = 'success';
    public const DEFAULT_VARIANT  = self::VARIANT_INFO;
    public const VARIANTS         = [
        self::VARIANT_INFO,
        self::VARIANT_WARNING,
        self::VARIANT_ERROR,
        self::VARIANT_SUCCESS,
    ];
    public const CATEGORY_ACTION  = 'action';
    public const CATEGORY_API     = 'api';
    public const CATEGORY_GENERAL = 'general';
    public const DEFAULT_CATEGORY = self::CATEGORY_API;
    public const CATEGORIES       = [
        self::CATEGORY_ACTION,
        self::CATEGORY_API,
        self::CATEGORY_GENERAL,
    ];

    protected $attributes = [
        'category' => self::DEFAULT_CATEGORY,
        'content'  => [],
        'tags'     => [],
        'timeout'  => false,
        'title'    => null,
        'variant'  => self::DEFAULT_VARIANT,
    ];

    protected $cast       = [
        'category' => 'string',
        'content'  => 'array',
        'tags'     => 'array',
        'timeout'  => 'bool',
        'title'    => 'string',
        'variant'  => 'string',
    ];

    /**
     * @return string[]
     */
    public function getContentAttribute(): array
    {
        return Arr::wrap($this->attributes['content'] ?? []);
    }
}

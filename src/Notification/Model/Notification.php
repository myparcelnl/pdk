<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string|null $title
 * @property string|null $content
 * @property string      $category
 * @property bool        $timeout
 * @property string      $variant
 */
class Notification extends Model
{
    final public const VARIANT_INFO    = 'info';
    final public const VARIANT_WARNING = 'warning';
    final public const VARIANT_ERROR   = 'error';
    final public const VARIANT_SUCCESS = 'success';
    final public const DEFAULT_VARIANT = self::VARIANT_INFO;
    final public const VARIANTS        = [
        self::VARIANT_INFO,
        self::VARIANT_WARNING,
        self::VARIANT_ERROR,
        self::VARIANT_SUCCESS,
    ];

    protected $attributes = [
        'title'    => null,
        'content'  => null,
        'category' => 'api',
        'timeout'  => false,
        'variant'  => self::DEFAULT_VARIANT,
    ];

    protected $cast       = [
        'title'    => 'string',
        'content'  => 'string',
        'category' => 'string',
        'timeout'  => 'bool',
        'variant'  => 'string',
    ];
}

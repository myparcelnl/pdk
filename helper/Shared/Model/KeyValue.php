<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Shared\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $key
 * @property string $value
 */
class KeyValue extends Model
{
    public    $attributes = [
        'key'   => null,
        'value' => null,
    ];

    protected $casts      = [
        'key' => 'string',
    ];
}

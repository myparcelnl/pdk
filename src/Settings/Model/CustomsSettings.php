<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $from
 * @property string $code
 * @property string $origin
 */
class CustomsSettings extends Model
{
    protected $attributes = [
        'from'   => null,
        'code'   => null,
        'origin' => null,
    ];

    protected $casts      = [
        'from'   => 'string',
        'code'   => 'string',
        'origin' => 'string',
    ];
}

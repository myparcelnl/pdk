<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $type
 * @property array  $enum
 * @property int    $minimum
 * @property int    $maximum
 * @property int    $minLength
 * @property int    $maxLength
 */
class Capability extends Model
{
    protected $attributes = [
        'type'      => null,
        'enum'      => null,
        'minimum'   => null,
        'maximum'   => null,
        'minLength' => null,
        'maxLength' => null,
    ];

    protected $casts      = [
        'type'      => 'string',
        'enum'      => 'array',
        'minimum'   => 'int',
        'maximum'   => 'int',
        'minLength' => 'int',
        'maxLength' => 'int',
    ];
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|int $height
 * @property null|int $length
 * @property null|int $width
 * @property null|int $weight
 */
class PhysicalProperties extends Model
{
    protected $attributes = [
        'height' => null,
        'length' => null,
        'width'  => null,
        'weight' => null,
    ];

    protected $casts      = [
        'height' => 'int',
        'length' => 'int',
        'width'  => 'int',
        'weight' => 'int',
    ];
}

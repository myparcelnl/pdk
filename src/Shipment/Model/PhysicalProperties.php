<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|int $height
 * @property null|int $length
 * @property int      $weight
 * @property null|int $width
 */
class PhysicalProperties extends Model
{
    protected $attributes = [
        'height' => null,
        'length' => null,
        'weight' => 0,
        'width'  => null,
    ];

    protected $casts      = [
        'height' => 'int',
        'length' => 'int',
        'weight' => 'int',
        'width'  => 'int',
    ];
}

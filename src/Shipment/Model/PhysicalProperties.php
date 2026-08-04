<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * Dimensions are in centimeters and the weight is in grams — the units the MyParcel API expects, so
 * they are sent as-is. Dimensions are optional: null means the merchant left the field empty and the
 * key is omitted from the request entirely, rather than sent as 0.
 *
 * @property null|int $height In centimeters.
 * @property null|int $length In centimeters.
 * @property null|int $width  In centimeters.
 * @property null|int $weight In grams.
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

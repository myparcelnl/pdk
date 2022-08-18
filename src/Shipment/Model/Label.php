<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $link
 * @property string $pdf
 */
class Label extends Model
{
    protected $attributes = [
        'link' => null,
        'pdf'  => null,
    ];

    protected $casts      = [
        'link' => 'string',
        'pdf'  => 'string',
    ];
}

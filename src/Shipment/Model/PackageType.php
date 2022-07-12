<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|int    $id
 * @property null|string $name
 */
class PackageType extends Model
{
    protected $attributes = [
        'id'   => null,
        'name' => null,
    ];

    protected $casts      = [
        'id'   => 'int',
        'name' => 'string',
    ];
}

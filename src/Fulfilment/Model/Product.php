<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string $uuid
 * @property null|string $sku
 * @property null|string $ean
 * @property null|string $externalIdentifier
 * @property null|string $name
 * @property null|string $description
 * @property null|string $width
 * @property null|string $length
 * @property null|string $height
 * @property null|string $weight
 */
class Product extends Model
{
    protected $attributes = [
        'uuid'               => null,
        'sku'                => null,
        'ean'                => null,
        'externalIdentifier' => null,
        'name'               => null,
        'description'        => null,
        'width'              => null,
        'length'             => null,
        'height'             => null,
        'weight'             => null,
    ];

    protected $casts      = [
        'uuid'               => 'string',
        'sku'                => 'string',
        'ean'                => 'string',
        'externalIdentifier' => 'string',
        'name'               => 'string',
        'description'        => 'string',
        'width'              => 'integer',
        'length'             => 'integer',
        'height'             => 'integer',
        'weight'             => 'integer',
    ];
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string                                    $uuid
 * @property null|string                                    $sku
 * @property null|string                                    $ean
 * @property null|string                                    $externalIdentifier
 * @property null|string                                    $name
 * @property null|string                                    $description
 * @property int                                            $width
 * @property int                                            $length
 * @property int                                            $height
 * @property int                                            $weight
 */
class Product extends Model
{
    /**
     * @var array
     */
    protected $attributes = [
        'uuid'               => null,
        'sku'                => null,
        'ean'                => null,
        'externalIdentifier' => null,
        'name'               => null,
        'description'        => null,
        'width'              => 0,
        'length'             => 0,
        'height'             => 0,
        'weight'             => 0,
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'uuid'               => 'string',
        'sku'                => 'string',
        'ean'                => 'string',
        'externalIdentifier' => 'string',
        'name'               => 'string',
        'description'        => 'string',
        'width'              => 'int',
        'length'             => 'int',
        'height'             => 'int',
        'weight'             => 'int',
    ];
}

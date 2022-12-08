<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Plugin\Model\PdkProduct;

/**
 * @property null|string $uuid
 * @property null|string $sku
 * @property null|string $ean
 * @property null|string $externalIdentifier
 * @property null|string $name
 * @property null|string $description
 * @property int         $width
 * @property int         $length
 * @property int         $height
 * @property int         $weight
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
     * @var array
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

    /**
     * @param  null|\MyParcelNL\Pdk\Plugin\Model\PdkProduct $product
     *
     * @return static
     */
    public static function fromPdkProduct(?PdkProduct $product): self
    {
        if (! $product) {
            return new self();
        }

        return new self([
            'sku'    => $product->sku,
            'ean'    => $product->ean,
            'name'   => $product->name,
            'weight' => $product->weight,
        ]);
    }
}

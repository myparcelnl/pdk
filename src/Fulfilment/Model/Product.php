<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Base\Model\Model;

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
     * @param  null|\MyParcelNL\Pdk\App\Order\Model\PdkProduct $product
     *
     * @return static
     */
    public static function fromPdkProduct(?PdkProduct $product): self
    {
        if (! $product) {
            return new self();
        }

        return new self([
            'sku'                => $product->sku,
            'ean'                => $product->ean,
            'name'               => $product->name,
            'weight'             => $product->weight,
            'width'              => $product->width,
            'length'             => $product->length,
            'height'             => $product->height,
            'externalIdentifier' => $product->externalIdentifier,
        ]);
    }
}

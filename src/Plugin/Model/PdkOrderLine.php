<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Fulfilment\Model\Product;

/**
 * todo use PdkProduct class not fulfilment product
 *
 * @property int                                           $quantity
 * @property int                                           $price
 * @property int                                           $vat
 * @property int                                           $priceAfterVat
 * @property null|\MyParcelNL\Pdk\Fulfilment\Model\Product $product
 */
class PdkOrderLine extends Model
{
    protected $attributes = [
        'quantity'      => 1,
        'price'         => 0,
        'vat'           => 0,
        'priceAfterVat' => 0,
        'product'       => null,
    ];

    protected $casts      = [
        'quantity'      => 'int',
        'price'         => 'int',
        'vat'           => 'int',
        'priceAfterVat' => 'int',
        'product'       => Product::class,
    ];
}

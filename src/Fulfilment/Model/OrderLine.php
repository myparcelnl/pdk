<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string                                   $uuid
 * @property null|int                                      $quantity
 * @property null|\MyParcelNL\Pdk\Base\Model\Currency      $price
 * @property null|\MyParcelNL\Pdk\Base\Model\Currency      $vat
 * @property null|\MyParcelNL\Pdk\Base\Model\Currency      $priceAfterVat
 * @property null|\MyParcelNL\Pdk\Fulfilment\Model\Product $product
 */
class OrderLine extends Model
{
    protected $attributes = [
        'uuid'          => null,
        'quantity'      => null,
        'price'         => null,
        'vat'           => null,
        'priceAfterVat' => null,
        'product'       => null,
    ];

    protected $casts      = [
        'uuid'          => 'string',
        'quantity'      => 'integer',
        'price'         => 'int',
        'vat'           => 'int',
        'priceAfterVat' => 'int',
        'product'       => Product::class,
    ];
}

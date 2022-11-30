<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Concern\HasPrices;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string                                   $uuid
 * @property int                                           $quantity
 * @property int                                           $price
 * @property int                                           $vat
 * @property int                                           $priceAfterVat
 * @property null|\MyParcelNL\Pdk\Fulfilment\Model\Product $product
 * @property string                                        $instructions
 * @property bool                                          $shippable
 */
class OrderLine extends Model
{
    use HasPrices;

    protected $attributes = [
        'uuid'          => null,
        'quantity'      => 1,
        'price'         => 0,
        'vat'           => 0,
        'priceAfterVat' => 0,
        'product'       => null,
    ];

    protected $casts      = [
        'uuid'          => 'string',
        'quantity'      => 'int',
        'price'         => 'int',
        'vat'           => 'int',
        'priceAfterVat' => 'int',
        'product'       => Product::class,
    ];

    /**
     * @param  null|array $data
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        $this->calculateVatTotals();
    }
}

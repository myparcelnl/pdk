<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Concern\HasPrices;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property int                                          $quantity
 * @property int                                          $price
 * @property int                                          $vat
 * @property int                                          $priceAfterVat
 * @property null|\MyParcelNL\Pdk\Plugin\Model\PdkProduct $product
 */
class PdkOrderLine extends Model
{
    use HasPrices;

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
        'product'       => PdkProduct::class,
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

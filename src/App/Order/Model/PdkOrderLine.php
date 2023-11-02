<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Base\Concern\HasPrices;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property int                                             $apiIdentifier
 * @property int                                             $quantity
 * @property string                                          $instructions
 * @property int                                             $price
 * @property int                                             $vat
 * @property int                                             $priceAfterVat
 * @property null|\MyParcelNL\Pdk\App\Order\Model\PdkProduct $product
 */
class PdkOrderLine extends Model
{
    use HasPrices;

    protected $attributes = [
        'apiIdentifier' => null,
        'quantity'      => 1,
        'instructions'  => '',
        'price'         => 0,
        'vat'           => 0,
        'priceAfterVat' => 0,
        'product'       => null,
    ];

    protected $casts      = [
        /**
         * UUID from the api if order was exported in order mode.
         */
        'apiIdentifier' => 'string',
        'quantity'      => 'int',
        'instructions'  => 'string',
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

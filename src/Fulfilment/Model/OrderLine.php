<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
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

        /**
         * Quantity of items.
         */
        'quantity'      => 1,

        /**
         * Price of a single item.
         */
        'price'         => 0,

        /**
         * VAT of a single item.
         */
        'vat'           => 0,

        /**
         * Price of a single item including VAT.
         */
        'priceAfterVat' => 0,

        'product' => null,
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
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        $this->calculateVatTotals();
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrderLine $pdkOrderLine
     *
     * @return self
     */
    public static function fromPdkOrderLine(PdkOrderLine $pdkOrderLine): self
    {
        $data = array_replace(
            $pdkOrderLine->toArray(),
            ['product' => Product::fromPdkProduct($pdkOrderLine->product)]
        );

        return new static($data);
    }
}

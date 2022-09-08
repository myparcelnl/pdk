<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderLineCollection;

/**
 * @property int $orderPrice
 * @property int $orderVat
 * @property int $orderPriceAfterVat
 * @property int $shipmentPrice
 * @property int $shipmentVat
 * @property int $shipmentPriceAfterVat
 * @property int $totalPrice
 * @property int $totalVat
 * @property int $totalPriceAfterVat
 */
class OrderTotals extends Model
{
    protected $attributes = [
        'orderPrice'            => null,
        'orderVat'              => null,
        'orderPriceAfterVat'    => null,
        'shipmentPrice'         => null,
        'shipmentVat'           => null,
        'shipmentPriceAfterVat' => null,
        'totalPrice'            => null,
        'totalVat'              => null,
        'totalPriceAfterVat'    => null,
    ];

    protected $casts      = [
        'orderPrice'            => 'int',
        'orderVat'              => 'int',
        'orderPriceAfterVat'    => 'int',
        'shipmentPrice'         => 'int',
        'shipmentVat'           => 'int',
        'shipmentPriceAfterVat' => 'int',
        'totalPrice'            => 'int',
        'totalVat'              => 'int',
        'totalPriceAfterVat'    => 'int',
    ];

    public static function getFromOrderData(
        ?OrderLineCollection $orderLines,
        ?int                 $shipmentPrice = 0,
        ?int                 $shipmentVat = 0
    ): self {
        $shipmentAfterVat = $shipmentPrice + $shipmentVat;
        $price            = 0;
        $priceAfterVat    = 0;

        if ($orderLines) {
            foreach ($orderLines as $orderLine) {
                $price         += $orderLine->quantity * $orderLine->getPrice();
                $priceAfterVat += $orderLine->quantity * $orderLine->getPriceAfterVat();
            }
        }

        return new self([
            'orderPrice'            => $price,
            'orderVat'              => $priceAfterVat - $price,
            'orderPriceAfterVat'    => $priceAfterVat,
            'shipmentPrice'         => $shipmentPrice,
            'shipmentVat'           => $shipmentVat,
            'shipmentPriceAfterVat' => $shipmentAfterVat,
            'totalPrice'            => $price + $shipmentPrice,
            'totalVat'              => $priceAfterVat + $shipmentAfterVat - $price - $shipmentPrice,
            'totalPriceAfterVat'    => $priceAfterVat + $shipmentAfterVat,
        ]);
    }
}

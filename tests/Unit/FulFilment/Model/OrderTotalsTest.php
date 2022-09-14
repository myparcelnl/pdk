<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Fulfilment\Model\OrderTotals;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

it('gets order totals', function ($input, $expected) {
    expect(
        OrderTotals::getFromOrderData(new PdkOrder($input))
            ->toArray()
    )->toBe($expected);
})->with([
    'two order lines' => [
        'input'    => [
            'shipmentPrice' => 200,
            'shipmentVat'   => 42,
            'orderLines'    => [
                [
                    'quantity'      => 1,
                    'price'         => 100,
                    'vat'           => 21,
                    'priceAfterVat' => 121,
                ],
                [
                    'quantity'      => 2,
                    'price'         => 1000,
                    'vat'           => 90,
                    'priceAfterVat' => 1090,
                ],
            ],
        ],
        'expected' => [
            'orderPrice'            => 2100,
            'orderVat'              => 201,
            'orderPriceAfterVat'    => 2301,
            'shipmentPrice'         => 200,
            'shipmentVat'           => 42,
            'shipmentPriceAfterVat' => 242,
            'totalPrice'            => 2300,
            'totalVat'              => 243,
            'totalPriceAfterVat'    => 2543,
        ],
    ],
]);

<?php
/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    $this->pdk = PdkFactory::create(MockPdkConfig::create());
});

it('instantiates shipments', function (array $input) {
    $order = new PdkOrder($input);

    expect($order->shipments)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and(
            $order->shipments->every(function ($item) {
                return is_a($item, Shipment::class);
            })
        );
})->with([
    'empty shipments' => [
        'input' => [
            'shipments' => [
                [],
            ],
        ],
    ],

    'no shipments' => [
        'input' => [
            'shipments' => [
            ],
        ],
    ],
]);

it('calculates correct totals', function (array $input, array $totals) {
    $order = new PdkOrder($input);

    expect(
        $order->only([
                'orderPrice',
                'orderVat',
                'orderPriceAfterVat',
                'totalPrice',
                'totalVat',
                'totalPriceAfterVat',
            ]
        )
    )->toEqual($totals);
})->with([
    'one line'  => [
        'input'  => [
            'shipmentPrice'         => 200,
            'shipmentPriceAfterVat' => 242,
            'shipmentVat'           => 42,
            'lines'                 => [
                [
                    'quantity'      => 2,
                    'price'         => 2,
                    'priceAfterVat' => 4,
                    'vat'           => 2,
                ],
            ],
        ],
        'totals' => [
            'orderPrice'         => 4,
            'orderVat'           => 4,
            'orderPriceAfterVat' => 8,
            'totalPrice'         => 204,
            'totalVat'           => 46,
            'totalPriceAfterVat' => 250,
        ],
    ],
    'two lines' => [
        'input'  => [
            'shipmentPrice'         => 100,
            'shipmentPriceAfterVat' => 120,
            'shipmentVat'           => 20,
            'lines'                 => [
                [
                    'quantity'      => 1,
                    'price'         => 20,
                    'priceAfterVat' => 24,
                    'vat'           => 4,
                ],
                [
                    'quantity'      => 4,
                    'price'         => 40,
                    'priceAfterVat' => 48,
                    'vat'           => 8,
                ],
            ],
        ],
        'totals' => [
            'orderPrice'         => 180,
            'orderPriceAfterVat' => 216,
            'orderVat'           => 36,
            'totalPrice'         => 280,
            'totalPriceAfterVat' => 336,
            'totalVat'           => 56,
        ],
    ],
]);

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

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

it('creates pdk order from fulfilment order', function (array $orders) {
    $orderCollection = new OrderCollection($orders);

    $pdkOrders = new PdkOrderCollection(
        $orderCollection
            ->map(function (Order $order) {
                return PdkOrder::fromFulfilmentOrder($order);
            })
            ->all()
    );

    assertMatchesJsonSnapshot(json_encode($pdkOrders->toArrayWithoutNull()));
})->with('fulfilmentOrders');

it('creates a storable array', function (array $orders) {
    $pdkOrders = new PdkOrderCollection($orders);
    $result    = [];

    foreach ($pdkOrders->all() as $pdkOrder) {
        $result[] = $pdkOrder->toStorableArray();
    }

    assertMatchesJsonSnapshot(json_encode($result));
})->with('pdkOrdersDomestic');

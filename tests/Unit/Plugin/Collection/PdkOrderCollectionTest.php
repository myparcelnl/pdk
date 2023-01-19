<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\src\Support\Arr;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());
it('holds PdkOrders', function () {
    $pdkOrderCollection = new PdkOrderCollection();

    $pdkOrderCollection->push(['externalIdentifier' => 'abc123']);
    $pdkOrderCollection->push(['externalIdentifier' => 'def456']);

    expect($pdkOrderCollection->count())
        ->toBe(2)
        ->and(
            $pdkOrderCollection->every(function ($pdkOrder) {
                return $pdkOrder instanceof PdkOrder;
            })
        )
        ->toBeTrue()
        ->and($pdkOrderCollection->every('externalIdentifier', '!=', null))
        ->toBeTrue();
});

it('can generate a shipment on each order', function () {
    $pdkOrderCollection = new PdkOrderCollection();

    $pdkOrderCollection->push(['externalIdentifier' => 'abc123']);
    $pdkOrderCollection->push(['externalIdentifier' => 'def456']);

    $pdkOrderCollection->generateShipments();
    $pdkOrderCollection->generateShipments();

    expect($pdkOrderCollection->count())
        ->toBe(2)
        ->and(
            $pdkOrderCollection->every(function (PdkOrder $order) {
                return 2 === $order->shipments->count();
            })
        )
        ->toBeTrue();
});

it('gets all shipments of all orders', function () {
    $pdkOrderCollection = new PdkOrderCollection();

    $pdkOrderCollection->push(['externalIdentifier' => 'abc123']);
    $pdkOrderCollection->push(['externalIdentifier' => 'def456']);

    $pdkOrderCollection->generateShipments();

    expect(
        $pdkOrderCollection->getAllShipments()
            ->count()
    )
        ->toBe(2);
});

it('updates order shipments by shipment ids', function () {
    $orders = new PdkOrderCollection([
        [
            'externalIdentifier' => '🐰',
            'shipments'          => [
                ['id' => 29090, 'status' => 1],
                ['id' => 30000, 'status' => 1],
            ],
        ],
        [
            'externalIdentifier' => '🐸',
            'shipments'          => [
                ['id' => 30010, 'status' => 1],
            ],
        ],
        [
            'externalIdentifier' => '🐷',
            'shipments'          => [],
        ],
        [
            'externalIdentifier' => '🦊',
            'shipments'          => [
                ['id' => 30070, 'status' => 1],
            ],
        ],
    ]);

    $orders->updateShipments(
        new ShipmentCollection([
            ['id' => 30000, 'status' => 7],
            ['id' => 30010, 'status' => 7],
            ['id' => 30020, 'status' => 7],
        ])
    );

    // TODO: simplify when collections support "only" method
    $shipments = array_map(function (array $shipment) {
        return Arr::only($shipment, ['id', 'orderId', 'status']);
    },
        $orders->getAllShipments()
            ->toArray());

    expect($shipments)->toBe([
        ['orderId' => '🐰', 'id' => 29090, 'status' => 1],
        ['orderId' => '🐰', 'id' => 30000, 'status' => 7],
        ['orderId' => '🐸', 'id' => 30010, 'status' => 7],
        ['orderId' => '🦊', 'id' => 30070, 'status' => 1],
    ]);
});

it('updates order shipments by order ids', function () {
    $orders = new PdkOrderCollection([
        [
            'externalIdentifier' => '🐰',
            'shipments'          => [
                ['id' => 29090, 'status' => 1],
                ['id' => 30000, 'status' => 1],
            ],
        ],
        [
            'externalIdentifier' => '🐸',
            'shipments'          => [
                ['id' => 30010, 'status' => 1],
            ],
        ],
        [
            'externalIdentifier' => '🐷',
            'shipments'          => [],
        ],
        [
            'externalIdentifier' => '🦊',
            'shipments'          => [
                ['id' => 30070, 'status' => 1],
            ],
        ],
    ]);

    $orders->updateShipments(
        new ShipmentCollection([
            ['orderId' => '🐰', 'id' => 30000, 'status' => 7],
            ['orderId' => '🐸', 'id' => 40000, 'status' => 7],
            ['orderId' => '🐷', 'id' => 30020, 'status' => 7],
        ])
    );

    // TODO: simplify when collections support "only" method
    $shipments = array_map(function (array $shipment) {
        return Arr::only($shipment, ['id', 'orderId', 'status']);
    },
        $orders->getAllShipments()
            ->toArray());

    expect($shipments)->toBe([
        ['orderId' => '🐰', 'id' => 29090, 'status' => 1],
        ['orderId' => '🐰', 'id' => 30000, 'status' => 7],
        ['orderId' => '🐸', 'id' => 30010, 'status' => 1],
        ['orderId' => '🐸', 'id' => 40000, 'status' => 7],
        ['orderId' => '🐷', 'id' => 30020, 'status' => 7],
        ['orderId' => '🦊', 'id' => 30070, 'status' => 1],
    ]);
});


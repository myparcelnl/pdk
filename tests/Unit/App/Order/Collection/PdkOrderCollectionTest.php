<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;

it('holds PdkOrders', function () {
    $pdkOrderCollection = new PdkOrderCollection([
        ['externalIdentifier' => 'MP-1'],
        ['externalIdentifier' => 'MP-2'],
    ]);

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
    $pdkOrderCollection = new PdkOrderCollection([
        new PdkOrder(['externalIdentifier' => 'MP-1', 'recipient' => ['cc' => 'NL']]),
        new PdkOrder(['externalIdentifier' => 'MP-2', 'recipient' => ['cc' => 'NL']]),
    ]);

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
    $pdkOrderCollection = new PdkOrderCollection([
        [
            'externalIdentifier' => 'MP-1',
            'shipments'          => [['id' => 100020]],
        ],
        [
            'externalIdentifier' => 'MP-2',
            'shipments'          => [['id' => 100021]],
        ],
    ]);

    $shipments = $pdkOrderCollection->getAllShipments();
    $array     = $shipments->toArray();

    expect($shipments)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and(Arr::pluck($array, 'id'))
        ->toEqual([100020, 100021])
        ->and(array_keys($array))
        ->toEqual([0, 1]);
});

it('gets shipments by shipment ids', function () {
    $pdkOrderCollection = new PdkOrderCollection([
        [
            'externalIdentifier' => '🐝',
            'shipments'          => [['id' => 29090], ['id' => 30000]],
        ],
        [
            'externalIdentifier' => '🦉',
            'shipments'          => [['id' => 30010]],
        ],
    ]);

    $shipments = $pdkOrderCollection->getShipmentsByIds([29090, 30010]);
    $array     = $shipments->toArray();

    expect($shipments)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and(Arr::pluck($array, 'id'))
        ->toEqual([29090, 30010])
        ->and(array_keys($array))
        ->toEqual([0, 1]);
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
            ['orderId' => '🐸', 'id' => 30010, 'status' => 3],
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
        ['orderId' => '🐸', 'id' => 30010, 'status' => 3],
        ['orderId' => '🐸', 'id' => 40000, 'status' => 7],
        ['orderId' => '🐷', 'id' => 30020, 'status' => 7],
        ['orderId' => '🦊', 'id' => 30070, 'status' => 1],
    ]);
});


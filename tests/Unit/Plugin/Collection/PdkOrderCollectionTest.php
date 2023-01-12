<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

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

it('can update shipments', function () {
    $pdkOrderCollection = new PdkOrderCollection();

    $pdkOrderCollection->push(['externalIdentifier' => 'wham', 'shipments' => [['id' => 59]]]);
    $pdkOrderCollection->push(['externalIdentifier' => 'last_christmas', 'shipments' => [['id' => 60]]]);

    $shipmentCollection = new ShipmentCollection([
        ['id' => 81, 'orderId' => 'wham'],
        ['id' => 82, 'orderId' => 'wham'],
        ['id' => 83, 'orderId' => 'citroen'],
    ]);

    $pdkOrderCollection = $pdkOrderCollection->updateShipments($shipmentCollection);

    /** @var \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order1 */
    $order1 = $pdkOrderCollection->firstWhere('externalIdentifier', 'wham');

    /** @var \MyParcelNL\Pdk\Plugin\Model\PdkOrder $order2 */
    $order2 = $pdkOrderCollection->firstWhere('externalIdentifier', 'last_christmas');

    expect($order1->shipments->count())
        ->toBe(2)
        ->and($order2->shipments->count())
        ->toBe(0)
        ->and($order1->shipments->firstWhere('id', 81))->not->toBeNull()
        ->and($order1->shipments->firstWhere('id', 82))->not->toBeNull();
});

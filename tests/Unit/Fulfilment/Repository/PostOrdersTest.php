<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollectionFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetOrdersResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrdersResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

it('creates a valid order collection from api data', function (array $input) {
    MockApi::enqueue(new ExamplePostOrdersResponse());

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $repository */
    $repository  = Pdk::get(OrderRepository::class);
    $savedOrders = $repository->postOrders(new OrderCollection($input));

    expect($savedOrders)
        ->toBeInstanceOf(OrderCollection::class);

    assertMatchesJsonSnapshot(json_encode($savedOrders->toArray()));
})->with('fulfilmentOrders');

it('creates order', function (PdkOrderCollectionFactory $factory) {
    MockApi::enqueue(new ExampleGetOrdersResponse());

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $repository */
    $repository = Pdk::get(OrderRepository::class);

    $pdkOrderCollection = $factory
        ->store()
        ->make();

    $orderCollection = new OrderCollection($pdkOrderCollection->map(function (PdkOrder $pdkOrder) {
        return Order::fromPdkOrder($pdkOrder);
    }));

    /** @var OrderRepository $response */
    $response = $repository->postOrders($orderCollection);
    $request  = MockApi::ensureLastRequest();

    $uri = $request->getUri();

    expect($uri->getPath())
        ->toBe('API/fulfilment/orders')
        ->and($response)
        ->toBeInstanceOf(OrderCollection::class);
})->with([
    'simple order' => [
        function () {
            return factory(PdkOrderCollection::class);
        },
    ],

    'order with drop-off point' => [
        function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)->withShipments([
                    factory(Shipment::class)->withDropOffPoint(),
                ])
            );
        },
    ],

    'order with shipment to pickup location' => [
        function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)->withShipments([
                    factory(Shipment::class)->withDeliveryOptionsWithPickupLocation(),
                ])
            );
        },
    ],
]);

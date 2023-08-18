<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\Base\Facade\MockApi;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetOrdersResponse;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

/**
 * @covers \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository::query
 */
it('creates order collection from queried data', function () {
    MockApi::enqueue(new ExampleGetOrdersResponse());

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $repository */
    $repository = Pdk::get(OrderRepository::class);

    $response = $repository->query([]);
    $order    = $response->first();
    $array    = $order->toArray();

    // No need to test this data here.
    $arrayWithoutCapabilities = Arr::except(
        $array,
        ['shipment.carrier.capabilities', 'shipment.carrier.returnCapabilities']
    );

    assertMatchesJsonSnapshot(json_encode($arrayWithoutCapabilities));
});

<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Repository;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetOrdersResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

/**
 * @covers \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository::query
 */
it('creates order collection from queried data', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = Pdk::get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExampleGetOrdersResponse());

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

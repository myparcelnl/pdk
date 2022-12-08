<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetOrdersResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

/**
 * @covers \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository::query
 */
it('creates order collection from queried data', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExampleGetOrdersResponse());

    /** @var \MyParcelNL\Pdk\Fulfilment\Repository\OrderRepository $repository */
    $repository = $pdk->get(OrderRepository::class);

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

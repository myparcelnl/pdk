<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsFromContractResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponseWithDropOffPoint;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponseWithPickup;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

/**
 * @covers \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository::query
 */

it('creates shipment collection from queried data', function (string $responseClass) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new $responseClass());

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = $pdk->get(ShipmentRepository::class);

    $response = $repository->query([]);
    $shipment = $response->first();
    $array    = $shipment->toArray();

    // No need to test this data here.
    $arrayWithoutCapabilities = Arr::except($array, ['carrier.capabilities', 'carrier.returnCapabilities']);

    expect($response)
        ->and($shipment->deliveryOptions->carrier)
        ->toBe($shipment->carrier->name);

    assertMatchesJsonSnapshot(json_encode($arrayWithoutCapabilities));
})->with([
    'normal shipment'              => [
        'response' => ExampleGetShipmentsResponse::class,
    ],
    'shipment with drop-off point' => [
        'response' => ExampleGetShipmentsResponseWithDropOffPoint::class,
    ],
    'shipment with pickup'         => [
        'response' => ExampleGetShipmentsResponseWithPickup::class,
    ],
    'shipment with contract'       => [
        'response' => ExampleGetShipmentsFromContractResponse::class,
    ],
]);

<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Repository;

use MyParcelNL\Pdk\Base\Facade\MockApi;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetShipmentsFromContractResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetShipmentsResponseWithDropOffPoint;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetShipmentsResponseWithPickup;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

it('creates shipment collection from queried data', function (string $responseClass) {
    MockApi::enqueue(new $responseClass());

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = Pdk::get(ShipmentRepository::class);

    $response = $repository->query([]);
    $shipment = $response->first();
    $array    = $shipment->toArray();

    // No need to test this data here.
    $arrayWithoutCapabilities = Arr::except($array, ['carrier.capabilities', 'carrier.returnCapabilities']);

    expect($response)
        ->and($shipment->deliveryOptions->carrier->externalIdentifier)
        ->toBe($shipment->carrier->externalIdentifier);

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

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Repository;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use RuntimeException;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

dataset('shipments', [
    'single shipment id'    => [
        [5],
        'path' => 'API/shipments/5',
    ],
    'multiple shipment ids' => [
        [5, 6],
        'path' => 'API/shipments/5;6',
    ],
]);

it(
    'gets shipments',
    function (array $collection, string $path) {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
        $api  = Pdk::get(ApiServiceInterface::class);
        $mock = $api->getMock();

        $mock->append(new ExampleGetShipmentsResponse());

        /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
        $repository = Pdk::get(ShipmentRepository::class);

        $response = $repository->getShipments($collection);
        $request  = $mock->getLastRequest();

        if (! $request) {
            throw new RuntimeException('No request was made');
        }

        $uri = $request->getUri();

        expect($uri->getPath())
            ->toBe($path)
            ->and($response)
            ->toBeInstanceOf(ShipmentCollection::class);
    }
)->with('shipments');

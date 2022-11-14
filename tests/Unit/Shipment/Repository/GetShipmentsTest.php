<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

$array              = array_fill(0, 30, 'appelboom');
$bulkShipmentsArray = array_map(function ($item, $index) {
    return ['id' => (int) $index + 1];
}, $array, array_keys($array));

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
        $pdk = PdkFactory::create(MockPdkConfig::create());
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
        $api  = $pdk->get(ApiServiceInterface::class);
        $mock = $api->getMock();

        $mock->append(new ExampleGetShipmentsResponse());

        /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
        $repository = $pdk->get(ShipmentRepository::class);

        $response = $repository->getShipments($collection);
        $request  = $mock->getLastRequest();

        if (! $request) {
            throw new RuntimeException('Request not found.');
        }

        $uri = $request->getUri();

        expect($uri->getPath())
            ->toBe($path)
            ->and($response)
            ->toBeInstanceOf(ShipmentCollection::class);
    }
)->with('shipments');

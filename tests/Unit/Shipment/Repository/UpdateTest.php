<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

/**
 * @covers \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository::update
 */

it('updates shipment', function ($args, $path, $query) {
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new Response());

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = $pdk->get(ShipmentRepository::class);

    $response = $repository->update(...$args);
    $request  = $mock->getLastRequest();

    if (! $request) {
        throw new RuntimeException('Request not found.');
    }

    $uri = $request->getUri();

    expect($uri->getQuery())
        ->toBe($query)
        ->and($uri->getPath())
        ->toBe($path)
        ->and($response)
        ->toBeInstanceOf(ShipmentCollection::class);
})->with([
    'multiple ids'                => [
        'args'  => [
            (new ShipmentCollection([
                ['id' => 5],
                ['id' => 6],
            ])),
            100,
        ],
        'path'  => 'API/shipments/5;6',
        'query' => 'size=100',
    ],
    'multiple reference ids'      => [
        'args'  => [
            new ShipmentCollection([
                ['referenceIdentifier' => 5],
                ['referenceIdentifier' => 6],
            ]),
            30,
        ],
        'path'  => 'API/shipments',
        'query' => 'reference_identifier=5%3B6&size=30',
    ],
    'both ids and reference ids'  => [
        'args'  => [
            (new ShipmentCollection([
                ['id' => 10, 'referenceIdentifier' => 'order-11'],
                ['id' => 55, 'referenceIdentifier' => 'order-12'],
            ])),
        ],
        'path'  => 'API/shipments/10;55',
        'query' => '',
    ],
    'one id and one reference id' => [
        'args'  => [
            (new ShipmentCollection([
                ['id' => 10],
                ['referenceIdentifier' => 'order-12'],
            ])),
            10,
        ],
        'path'  => 'API/shipments/10',
        'query' => 'size=10',
    ],
]);

it('throws error when updating collection without ids or reference ids', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new Response());

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = $pdk->get(ShipmentRepository::class);

    $repository->update(
        new ShipmentCollection([
            ['carrier' => 'postnl'],
            ['carrier' => 'instabox'],
        ])
    );
})->throws(InvalidArgumentException::class);

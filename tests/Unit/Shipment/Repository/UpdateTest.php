<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\ShipmentRepository;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Facade\MockApi;

/**
 * @covers \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository::update
 */

it('updates shipment', function ($args, $path, $query) {
    PdkFactory::create(MockPdkConfig::DEFAULT_CONFIG);
    MockApi::getMock()
        ->append(new Response());

    $response = ShipmentRepository::update(...$args);
    $request  = MockApi::getMock()
        ->getLastRequest();

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
    PdkFactory::create(MockPdkConfig::DEFAULT_CONFIG);
    MockApi::getMock()
        ->append(new Response());

    ShipmentRepository::update(
        new ShipmentCollection([
            ['carrier' => 'postnl'],
            ['carrier' => 'instabox'],
        ])
    );
})->throws(InvalidArgumentException::class);

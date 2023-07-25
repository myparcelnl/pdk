<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Repository;

use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('updates shipment', function (array $collection, ?int $size, $path, $query) {
    MockApi::enqueue(new Response());

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = Pdk::get(ShipmentRepository::class);

    $response = $repository->update(new ShipmentCollection($collection), $size);
    $request  = MockApi::ensureLastRequest();

    $uri = $request->getUri();

    expect($uri->getQuery())
        ->toBe($query)
        ->and($uri->getPath())
        ->toBe($path)
        ->and($response)
        ->toBeInstanceOf(ShipmentCollection::class);
})->with([
    'multiple ids'                => [
        'collection' => [
            ['id' => 5],
            ['id' => 6],
        ],

        'size'  => 100,
        'path'  => 'API/shipments/5;6',
        'query' => 'size=100',
    ],
    'multiple reference ids'      => [
        'collection' => [
            ['referenceIdentifier' => 5],
            ['referenceIdentifier' => 6],
        ],
        'size'       => 30,
        'path'       => 'API/shipments',
        'query'      => 'reference_identifier=5%3B6&size=30',
    ],
    'both ids and reference ids'  => [
        'collection' => [
            ['id' => 10, 'referenceIdentifier' => 'order-11'],
            ['id' => 55, 'referenceIdentifier' => 'order-12'],
        ],
        'size'       => null,
        'path'       => 'API/shipments/10;55',
        'query'      => '',
    ],
    'one id and one reference id' => [
        'collection' => [
            ['id' => 10],
            ['referenceIdentifier' => 'order-12'],
        ],
        'size'       => 10,
        'path'       => 'API/shipments/10',
        'query'      => 'size=10',
    ],
]);

it('throws error when updating collection without ids or reference ids', function () {
    MockApi::enqueue(new Response());

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = Pdk::get(ShipmentRepository::class);

    $repository->update(
        new ShipmentCollection([
            ['carrier' => 'postnl'],
            ['carrier' => 'instabox'],
        ])
    );
})->throws(InvalidArgumentException::class);

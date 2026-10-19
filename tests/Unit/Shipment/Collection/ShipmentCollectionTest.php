<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Collection;

use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('stores shipments as a list after removing deleted shipments', function () {
    $collection = new ShipmentCollection([
        ['id' => 1, 'deleted' => '2026-09-29 10:00:00'],
        ['id' => 2],
        ['id' => 3],
    ]);

    $storable = $collection->toStorableArray();

    expect(array_keys($storable))
        ->toBe([0, 1])
        ->and(array_column($storable, 'id'))
        ->toBe([2, 3]);
});

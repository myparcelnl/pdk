<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

function createLabelsCollection(int $count): ShipmentCollection
{
    return new ShipmentCollection(array_map(static function (int $id) {
        return ['id' => $id];
    }, range(1, $count)));
}

it('uses the v2 endpoint from the bulk threshold on', function (int $count, bool $expected) {
    $collection = createLabelsCollection($count);
    $request    = new GetLabelsRequest($collection, []);

    expect(GetLabelsRequest::usesV2($collection))
        ->toBe($expected)
        ->and(strpos($request->getPath(), 'v2/') === 0)
        ->toBe($expected);
})->with([
    'below threshold' => [24, false],
    'at threshold'    => [25, true],
]);

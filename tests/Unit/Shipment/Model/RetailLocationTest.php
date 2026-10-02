<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Tests\Uses\UsesMockEachLogger;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\OrderApi\Model\PickupAnyOfLocation;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesMockEachLogger());

it('stores the pickup location type as an Order API location type', function (?string $input, ?string $expected) {
    $location = new RetailLocation(['type' => $input]);

    expect($location->type)
        ->toBe($expected)
        ->and(Logger::getLogs())
        ->toBe([]);
})->with([
    'no type'                 => [null, null],
    'checkout parcel locker'  => ['locker', PickupAnyOfLocation::TYPE_PARCEL_LOCKER],
    'checkout other location' => ['default', null],
    'Order API parcel locker' => [PickupAnyOfLocation::TYPE_PARCEL_LOCKER, PickupAnyOfLocation::TYPE_PARCEL_LOCKER],
    'Order API post office'   => [PickupAnyOfLocation::TYPE_POST_OFFICE, PickupAnyOfLocation::TYPE_POST_OFFICE],
]);

it('stores null and logs a warning for an unknown pickup location type', function (string $input) {
    $location = new RetailLocation(['type' => $input]);
    $logs     = Logger::getLogs();

    expect($location->type)
        ->toBeNull()
        ->and($logs)
        ->toHaveCount(1)
        ->and($logs[0]['level'])
        ->toBe('warning')
        ->and($logs[0]['context'])
        ->toBe(['type' => $input]);
})->with([
    'other casing' => ['Locker'],
    'unknown'      => ['retail_point'],
]);

it('keeps the pickup location type after storing and loading it', function () {
    $stored = (new RetailLocation(['type' => 'locker']))->toStorableArray();

    expect((new RetailLocation($stored))->type)->toBe(PickupAnyOfLocation::TYPE_PARCEL_LOCKER);
});

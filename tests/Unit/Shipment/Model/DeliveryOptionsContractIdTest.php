<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesAccountMock());

it('stores contractId when provided', function () {
    $deliveryOptions = new DeliveryOptions(['contractId' => 123]);
    expect($deliveryOptions->contractId)->toBe(123);
});

it('defaults contractId to null', function () {
    expect((new DeliveryOptions())->contractId)->toBeNull();
});

it('includes contractId in toArray output', function () {
    $do = new DeliveryOptions(['contractId' => 456]);
    expect($do->toArray())->toHaveKey('contractId', 456);
});

it('includes contractId in toStorableArray output', function () {
    $do = new DeliveryOptions(['contractId' => 789]);
    expect($do->toStorableArray())->toHaveKey('contractId', 789);
});

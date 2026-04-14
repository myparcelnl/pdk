<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Model;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('allows setting and getting contractId as a transient property', function () {
    $carrier = factory(Carrier::class)
        ->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->make();

    $carrier->contractId = 42;

    expect($carrier->contractId)->toBe(42);
});

it('defaults contractId to null', function () {
    $carrier = factory(Carrier::class)
        ->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->make();

    expect($carrier->contractId)->toBeNull();
});

it('does not include contractId in toArray output', function () {
    $carrier = factory(Carrier::class)
        ->withAllCapabilities(RefCapabilitiesSharedCarrierV2::POSTNL)
        ->make();

    $carrier->contractId = 42;

    expect($carrier->toArray())->not->toHaveKey('contractId');
});

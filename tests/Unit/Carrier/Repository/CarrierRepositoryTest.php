<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\mockPlatform;

usesShared(new UsesEachMockPdkInstance());

it('migrates deprecated carrier name "ups" to UPS Standard', function () {
    $reset = mockPlatform('myparcel');

    $repository = new CarrierRepository(Pdk::get(StorageInterface::class));
    $carrier    = $repository->get(['name' => Carrier::CARRIER_UPS_NAME]);

    expect($carrier)->not->toBeNull();
    expect($carrier->id)->toBe(Carrier::CARRIER_UPS_STANDARD_ID);
    expect($carrier->name)->toBe(Carrier::CARRIER_UPS_STANDARD_NAME);

    $reset();
});

it('returns null for deprecated carrier 8 since it no longer exists', function () {
    $reset = mockPlatform('myparcel');

    $repository = new CarrierRepository(Pdk::get(StorageInterface::class));
    $carrier    = $repository->get(['id' => Carrier::CARRIER_UPS_ID]);

    expect($carrier)->toBeNull();

    $reset();
});

it('does not migrate non-deprecated carriers', function () {
    $reset = mockPlatform('myparcel');

    $repository = new CarrierRepository(Pdk::get(StorageInterface::class));
    $carrier    = $repository->get(['id' => Carrier::CARRIER_POSTNL_ID]);

    expect($carrier->id)->toBe(Carrier::CARRIER_POSTNL_ID);
    expect($carrier->name)->toBe(Carrier::CARRIER_POSTNL_NAME);

    $reset();
});

it('returns null for unknown carrier', function () {
    $reset = mockPlatform('myparcel');

    $repository = new CarrierRepository(Pdk::get(StorageInterface::class));
    $carrier    = $repository->get(['id' => 999]);

    expect($carrier)->toBeNull();

    $reset();
});

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesEachMockPdkInstance());

it('migrates deprecated carrier name "ups" to UPS Standard', function () {
    TestBootstrapper::forPlatform('myparcel');

    $repository = Pdk::get(CarrierRepository::class);
    $carrier    = $repository->get(['name' => Carrier::CARRIER_UPS_NAME]);

    expect($carrier)->not->toBeNull();
    expect($carrier->id)->toBe(Carrier::CARRIER_UPS_STANDARD_ID);
    expect($carrier->name)->toBe(Carrier::CARRIER_UPS_STANDARD_NAME);
});

it('returns null for deprecated carrier 8 since it no longer exists', function () {
    TestBootstrapper::forPlatform('myparcel');

    $repository = Pdk::get(CarrierRepository::class);
    $carrier    = $repository->get(['id' => Carrier::CARRIER_UPS_ID]);

    expect($carrier)->toBeNull();
});

it('does not migrate non-deprecated carriers', function () {
    TestBootstrapper::forPlatform('myparcel');

    $repository = Pdk::get(CarrierRepository::class);
    $carrier    = $repository->get(['id' => Carrier::CARRIER_POSTNL_ID]);

    expect($carrier->id)->toBe(Carrier::CARRIER_POSTNL_ID);
    expect($carrier->name)->toBe(Carrier::CARRIER_POSTNL_NAME);
});

it('returns null for unknown carrier', function () {
    TestBootstrapper::forPlatform('myparcel');

    $repository = Pdk::get(CarrierRepository::class);
    $carrier    = $repository->get(['id' => 999]);

    expect($carrier)->toBeNull();
});

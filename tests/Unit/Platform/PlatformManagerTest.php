<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Facade\Platform as PlatformFacade;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;

use function Spatie\Snapshots\assertMatchesJsonSnapshot;

it('retrieves config for each platform', function (string $platform) {
    TestBootstrapper::forPlatform($platform);

    $defaults = PlatformFacade::all();

    assertMatchesJsonSnapshot(json_encode($defaults));
})->with('platforms');

it('gets specific keys from platform data', function () {
    TestBootstrapper::forPlatform(Platform::SENDMYPARCEL_NAME);

    expect(PlatformFacade::get('name'))
        ->toBe('belgie')
        ->and(PlatformFacade::get('human'))
        ->toBe('SendMyParcel')
        ->and(PlatformFacade::get('localCountry'))
        ->toBe('BE')
        ->and(PlatformFacade::get('defaultCarrier'))
        ->toBe('BPOST')
        ->and(PlatformFacade::get('nonExistingKey'))
        ->toBeNull();
});

it('gets carriers', function (string $platform) {
    TestBootstrapper::forPlatform($platform);

    $carriers = PlatformFacade::getCarriers();

    expect($carriers)->toBeInstanceOf(CarrierCollection::class);

    assertMatchesJsonSnapshot(json_encode($carriers->toArrayWithoutNull()));
})->with('platforms');

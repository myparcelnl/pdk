<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

use InvalidArgumentException;
use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Facade\Platform as PlatformFacade;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;
use function Spatie\Snapshots\assertMatchesSnapshot;

it('retrieves config for each platform', function (string $platform) {
    MockPdkFactory::create(['platform' => $platform]);

    $defaults = PlatformFacade::all();

    assertMatchesSnapshot($defaults);
})->with('platforms');

it('gets specific keys from platform data', function () {
    MockPdkFactory::create(['platform' => Platform::FLESPAKKET_NAME]);

    expect(PlatformFacade::get('name'))
        ->toBe('flespakket')
        ->and(PlatformFacade::get('human'))
        ->toBe('Flespakket')
        ->and(PlatformFacade::get('localCountry'))
        ->toBe('NL')
        ->and(PlatformFacade::get('defaultCarrier'))
        ->toBe('postnl')
        ->and(PlatformFacade::get('nonExistingKey'))
        ->toBeNull();
});

it('gets carriers', function (string $platform) {
    MockPdkFactory::create(['platform' => $platform]);

    $carriers = PlatformFacade::getCarriers();

    expect($carriers)->toBeInstanceOf(CarrierCollection::class);

    assertMatchesJsonSnapshot(json_encode($carriers->toArrayWithoutNull()));
})->with('platforms');

it('throws error when platform does not exist', function () {
    MockPdkFactory::create(['platform' => 'nonExistingPlatform']);

    PlatformFacade::all();
})->throws(InvalidArgumentException::class);

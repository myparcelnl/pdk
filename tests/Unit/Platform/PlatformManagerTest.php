<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function DI\value;
use function Spatie\Snapshots\assertMatchesSnapshot;

it('retrieves config for each platform', function (string $platform) {
    PdkFactory::create(
        MockPdkConfig::create([
            'platform' => value($platform),
        ])
    );

    $defaults = Platform::all();

    assertMatchesSnapshot($defaults);
})->with('platforms');

it('gets specific keys from platform data', function () {
    PdkFactory::create(MockPdkConfig::create(['platform' => \MyParcelNL\Pdk\Account\Platform::FLESPAKKET_NAME]));

    expect(Platform::get('name'))
        ->toBe('flespakket')
        ->and(Platform::get('human'))
        ->toBe('Flespakket')
        ->and(Platform::get('localCountry'))
        ->toBe('NL')
        ->and(Platform::get('defaultCarrier'))
        ->toBe('postnl')
        ->and(Platform::get('nonExistingKey'))
        ->toBeNull();
});

it('throws error when platform does not exist', function () {
    PdkFactory::create(MockPdkConfig::create(['platform' => 'nonExistingPlatform']));

    Platform::all();
})->throws(InvalidArgumentException::class);

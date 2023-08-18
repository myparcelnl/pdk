<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Platform;

use InvalidArgumentException;
use MyParcelNL\Pdk\Facade\Platform;
use function DI\value;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function Spatie\Snapshots\assertMatchesSnapshot;

it('retrieves config for each platform', function (string $platform) {
    mockPdkProperties(['platform' => value($platform)]);

    $defaults = Platform::all();

    assertMatchesSnapshot($defaults);
})->with('platforms');

it('gets specific keys from platform data', function () {
    mockPdkProperties(['platform' => \MyParcelNL\Pdk\Account\Platform::FLESPAKKET_NAME]);

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
    mockPdkProperty('platform', 'nonExistingPlatform');

    Platform::all();
})->throws(InvalidArgumentException::class);

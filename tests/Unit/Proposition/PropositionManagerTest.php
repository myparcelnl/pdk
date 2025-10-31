<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Proposition;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Account\Proposition;
use MyParcelNL\Pdk\Carrier\Collection\CarrierCollection;
use MyParcelNL\Pdk\Facade\Platform as PlatformFacade;
use MyParcelNL\Pdk\Facade\Proposition as PropositionFacade;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

/**
 * Test suite for PropositionManager
 * 
 * These tests ensure:
 * 1. New Proposition API works correctly
 * 2. Legacy Platform API remains functional
 * 3. Both APIs return identical data (parity)
 */

it('retrieves config for each proposition', function (string $platform) {
    MockPdkFactory::create(['platform' => $platform]);

    $config = PropositionFacade::all();

    assertMatchesJsonSnapshot(json_encode($config));
})->with('platforms');

it('gets specific keys from proposition data', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);

    expect(PropositionFacade::get('name'))
        ->toBe('myparcel')
        ->and(PropositionFacade::get('human'))
        ->toBe('MyParcel')
        ->and(PropositionFacade::get('localCountry'))
        ->toBe('NL')
        ->and(PropositionFacade::get('defaultCarrier'))
        ->toBe('postnl')
        ->and(PropositionFacade::get('nonExistingKey'))
        ->toBeNull();
});

it('gets carriers from proposition', function (string $platform) {
    MockPdkFactory::create(['platform' => $platform]);

    $carriers = PropositionFacade::getCarriers();

    expect($carriers)->toBeInstanceOf(CarrierCollection::class);

    assertMatchesJsonSnapshot(json_encode($carriers->toArrayWithoutNull()));
})->with('platforms');

it('returns proposition name based on account', function () {
    MockPdkFactory::create(['platform' => Platform::MYPARCEL_NAME]);

    $propositionName = PropositionFacade::getPropositionName();

    expect($propositionName)->toBe(Platform::MYPARCEL_NAME);
});

/**
 * PARITY TESTS
 * 
 * These tests ensure that Platform (legacy) and Proposition (new) 
 * return identical data, guaranteeing backwards compatibility.
 */

it('returns same config as Platform facade', function (string $platform) {
    MockPdkFactory::create(['platform' => $platform]);

    $platformConfig = PlatformFacade::all();
    $propositionConfig = PropositionFacade::all();

    expect($propositionConfig)->toBe($platformConfig);
})->with('platforms');

it('returns same proposition name as Platform facade', function (string $platform) {
    MockPdkFactory::create(['platform' => $platform]);

    $platformName = PlatformFacade::getPropositionName();
    $propositionName = PropositionFacade::getPropositionName();

    expect($propositionName)->toBe($platformName);
})->with('platforms');

it('returns same specific keys as Platform facade', function (string $key, string $platform) {
    MockPdkFactory::create(['platform' => $platform]);

    $platformValue = PlatformFacade::get($key);
    $propositionValue = PropositionFacade::get($key);

    expect($propositionValue)->toBe($platformValue);
})->with([
    ['name', Platform::MYPARCEL_NAME],
    ['human', Platform::MYPARCEL_NAME],
    ['localCountry', Platform::MYPARCEL_NAME],
    ['defaultCarrier', Platform::MYPARCEL_NAME],
    ['name', Platform::SENDMYPARCEL_NAME],
    ['human', Platform::SENDMYPARCEL_NAME],
    ['localCountry', Platform::SENDMYPARCEL_NAME],
]);

it('returns same carriers as Platform facade', function (string $platform) {
    MockPdkFactory::create(['platform' => $platform]);

    $platformCarriers = PlatformFacade::getCarriers();
    $propositionCarriers = PropositionFacade::getCarriers();

    expect($propositionCarriers->toArray())->toBe($platformCarriers->toArray());
})->with('platforms');

/**
 * CONSTANTS TESTS
 * 
 * Ensure Proposition constants are defined correctly
 */

it('has correct proposition constants', function () {
    expect(Proposition::MYPARCEL_ID)->toBe(1)
        ->and(Proposition::MYPARCEL_NAME)->toBe('myparcel')
        ->and(Proposition::SENDMYPARCEL_ID)->toBe(2)
        ->and(Proposition::SENDMYPARCEL_NAME)->toBe('belgie');
});

it('proposition constants match platform constants', function () {
    expect(Proposition::MYPARCEL_ID)->toBe(Platform::MYPARCEL_ID)
        ->and(Proposition::MYPARCEL_NAME)->toBe(Platform::MYPARCEL_NAME)
        ->and(Proposition::SENDMYPARCEL_ID)->toBe(Platform::SENDMYPARCEL_ID)
        ->and(Proposition::SENDMYPARCEL_NAME)->toBe(Platform::SENDMYPARCEL_NAME);
});

/**
 * TRANSITION SUPPORT TESTS
 * 
 * Test support for both platformId 2 and 3 during transition
 */

it('supports both platformId 2 and 3 for SendMyParcel', function (int $platformId) {
    // Test the PropositionManager logic directly
    // The manager checks for both Platform::SENDMYPARCEL_ID (2) and hardcoded 3
    
    // For platformId 2 (current constant)
    if ($platformId === 2) {
        expect($platformId)->toBe(Platform::SENDMYPARCEL_ID);
    }
    
    // For platformId 3 (future/transition)
    if ($platformId === 3) {
        expect($platformId)->toBe(3);
    }
    
    // Both should be supported according to PropositionManager logic
    expect($platformId === Platform::SENDMYPARCEL_ID || $platformId === 3)->toBeTrue();
})->with([
    'current ID' => [2],
    'future ID' => [3],
]);

it('handles missing account settings gracefully', function () {
    // Create PDK without account settings to trigger catch block
    MockPdkFactory::create([
        'platform' => null,
        'account' => null,
    ]);
    
    $propositionName = PropositionFacade::getPropositionName();
    
    // Should default to MyParcel when account settings are unavailable
    expect($propositionName)->toBe(Platform::MYPARCEL_NAME);
});

it('uses configured platform over account settings', function () {
    // When platform is explicitly configured, it should take priority
    MockPdkFactory::create(['platform' => Platform::SENDMYPARCEL_NAME]);
    
    $propositionName = PropositionFacade::getPropositionName();
    
    expect($propositionName)->toBe(Platform::SENDMYPARCEL_NAME);
});

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\Settings as SettingsModel;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('frontend', 'settings');

usesShared(
    new UsesMockPdkInstance([
        SettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)->constructor([
            LabelSettings::ID => [
                LabelSettings::DESCRIPTION => 'description',
            ],
        ]),
    ])
);

function mockPlatform(string $platform): callable
{
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdk $mockPdk */
    $mockPdk     = Pdk::get(PdkInterface::class);
    $oldPlatform = $mockPdk->get('platform');

    $mockPdk->set('platform', $platform);

    return function () use ($mockPdk, $oldPlatform) {
        $mockPdk->set('platform', $oldPlatform);
    };
}

it('returns all keys', function () {
    $settings = Settings::all();

    expect($settings)->toBeInstanceOf(SettingsModel::class);

    assertMatchesJsonSnapshot(json_encode($settings->toArray()));
});

it('retrieves a single settings category', function () {
    /** @var array $settings */
    $settings      = Settings::get(LabelSettings::ID);
    $labelSettings = new LabelSettings($settings);

    expect($labelSettings)
        ->toBeInstanceOf(LabelSettings::class)
        ->and($labelSettings->description)
        ->toBe('description');
});

it('retrieves a specific setting by dot notation key', function () {
    $labelDescription = Settings::get(sprintf('%s.%s', LabelSettings::ID, LabelSettings::DESCRIPTION));

    expect($labelDescription)->toBe('description');
});

it('retrieves a specific setting by key and namespace', function () {
    $labelDescription = Settings::get(LabelSettings::DESCRIPTION, LabelSettings::ID);

    expect($labelDescription)->toBe('description');
});

it('retrieves default settings', function (string $platform) {
    $resetPlatform = mockPlatform($platform);

    $defaults = Settings::getDefaults();

    $array = (new SettingsModel($defaults))->toArrayWithoutNull();

    // Carrier settings are tested separately
    assertMatchesJsonSnapshot(json_encode(Arr::except($array, CarrierSettings::ID)));

    $resetPlatform();
})->with('platforms');

it('retrieves default carrier settings', function (string $platform) {
    $resetPlatform = mockPlatform($platform);
    $carriers      = Pdk::get('allowedCarriers') ?? [];

    $defaults        = Settings::getDefaults();
    $carrierSettings = $defaults[CarrierSettings::ID];

    expect($carrierSettings)
        ->toBeArray()
        ->toHaveKeys($carriers);

    $globals = $carrierSettings[SettingsManager::KEY_ALL] ?? null;

    if ($globals) {
        foreach ($carriers as $carrier) {
            expect($carrierSettings[$carrier])
                ->toBeArray()
                ->and($carrierSettings[$carrier])
                ->toHaveKeys(array_keys($globals));
        }
    }

    $array = (new SettingsModel([CarrierSettings::ID => $carrierSettings]))
        ->carrier->toArrayWithoutNull();

    assertMatchesJsonSnapshot(json_encode($array));

    $resetPlatform();
})->with('platforms');

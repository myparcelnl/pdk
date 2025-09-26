<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\Settings as SettingsModel;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('frontend', 'settings');

usesShared(
    new UsesMockPdkInstance([
        PdkSettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)->constructor([
            LabelSettings::ID => [
                LabelSettings::DESCRIPTION => 'description',
            ],
        ]),
    ])
);

it('returns all keys', function () {
    $settings = Settings::all();

    expect($settings)->toBeInstanceOf(SettingsModel::class);

    assertMatchesJsonSnapshot(json_encode($settings->toArrayWithoutNull()));
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
    TestBootstrapper::forPlatform($platform);

    $defaults = Settings::getDefaults();

    $array = (new SettingsModel($defaults))->except(CarrierSettings::ID, Arrayable::SKIP_NULL);

    // Carrier settings are tested separately
    assertMatchesJsonSnapshot(json_encode($array));
})->with('platforms');

it('retrieves default carrier settings', function (string $platform) {
    TestBootstrapper::forPlatform($platform);

    $carriers = (new Collection(Platform::getCarriers()))
        ->pluck('name')
        ->map(fn (string $name) => Pdk::get(PropositionService::class)->mapNewToLegacyCarrierName($name))
        ->toArray();

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
})->with('platforms');

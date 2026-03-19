<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\AccountSettings;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Proposition\Proposition;
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

\beforeEach(function () {
    // Reset active proposition ID between tests.
    Pdk::get(PropositionService::class)->clearActivePropositionId();
});

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

it('retrieves default settings', function (int $propositionId) {
    TestBootstrapper::forProposition($propositionId);

    $defaults = Settings::getDefaults();

    $array = (new SettingsModel($defaults))->except(CarrierSettings::ID, Arrayable::SKIP_NULL);

    // Test that default settings have expected structure
    expect($array)->toBeArray()
        ->and($array)->toHaveKey(LabelSettings::ID)
        ->and($array[LabelSettings::ID])->toBeArray();
})->with([
    [Proposition::MYPARCEL_ID],
    [Proposition::SENDMYPARCEL_ID],
]);

it('retrieves default carrier settings', function (int $propositionId) {
    TestBootstrapper::forProposition($propositionId);

    // Get carrier names in new format (POSTNL, DHL_FOR_YOU, etc.)
    $carriers = AccountSettings::getCarriers()
        ->pluck('carrier')  // Extract carrier names as strings in new format
        ->filter()  // Remove any null values
        ->values()  // Re-index array
        ->toArray();

    $defaults        = Settings::getDefaults();
    $carrierSettings = $defaults[CarrierSettings::ID];

    // Test that carrier settings have expected structure with new carrier names
    expect($carrierSettings)->toBeArray()
        ->and($carrierSettings)->toHaveKeys($carriers);

    $globals = $carrierSettings[SettingsManager::KEY_ALL] ?? null;

    if ($globals) {
        foreach ($carriers as $carrier) {
            expect($carrierSettings[$carrier])
                ->toBeArray()
                ->and($carrierSettings[$carrier])
                ->toHaveKeys(array_keys($globals));
        }
    }

    // Verify carrier settings object can be created
    $carrierSettingsModel = (new SettingsModel([CarrierSettings::ID => $carrierSettings]))->carrier;

    expect($carrierSettingsModel)->toBeInstanceOf(CarrierSettings::class)
        ->and($carrierSettingsModel->toArrayWithoutNull())->toBeArray();
})->with([
    [Proposition::MYPARCEL_ID],
    [Proposition::SENDMYPARCEL_ID],
]);

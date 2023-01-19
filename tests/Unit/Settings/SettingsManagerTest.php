<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\Settings as SettingsModel;
use MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface;
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

it('returns all keys', function () {
    $settings = Settings::all();

    expect($settings)->toBeInstanceOf(SettingsModel::class);

    assertMatchesJsonSnapshot(json_encode($settings->toArray()));
});

it('retrieves a single settings category', function () {
    /** @var LabelSettings $labelSettings */
    $labelSettings = Settings::get(LabelSettings::ID);

    expect($labelSettings)
        ->toBeInstanceOf(LabelSettings::class)
        ->and($labelSettings->description)
        ->toBe('description');
});

it('retrieves a specific setting by dot notation key', function () {
    $labelDescription = Settings::get(LabelSettings::ID . '.' . LabelSettings::DESCRIPTION);

    expect($labelDescription)->toBe('description');
});

it('retrieves a specific setting by key and namespace', function () {
    $labelDescription = Settings::get(LabelSettings::DESCRIPTION, LabelSettings::ID);

    expect($labelDescription)->toBe('description');
});

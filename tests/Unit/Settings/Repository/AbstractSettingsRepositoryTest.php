<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use function MyParcelNL\Pdk\Tests\factory;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

uses()->group('settings');

beforeEach(function () {
    factory(\MyParcelNL\Pdk\Settings\Model\Settings::class)
        ->withAccount([
            AccountSettings::API_KEY => '1234567890',
        ])
        ->withCarrierPostNl([
            CarrierSettings::ALLOW_DELIVERY_OPTIONS => true,
            CarrierSettings::CUTOFF_TIME            => '17:00',
        ])
        ->withCarrierDhlForYou([
            CarrierSettings::ALLOW_DELIVERY_OPTIONS => false,
        ])
        ->withCarrierBpost([
            CarrierSettings::ALLOW_DELIVERY_OPTIONS => true,
        ])
        ->store();
});

it('retrieves all categories and fields', function () {
    /** @var \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface $repository */
    $repository = Pdk::get(SettingsRepositoryInterface::class);
    $settings   = $repository->all();

    assertMatchesJsonSnapshot(json_encode($settings->toArray()));
});

it('retrieves a single setting from a category', function (string $key, $expected) {
    /** @var \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface $repository */
    $repository        = Pdk::get(SettingsRepositoryInterface::class);
    $createSettingsKey = Pdk::get('createSettingsKey');

    expect($repository->get($createSettingsKey($key)))->toBe($expected);
})->with([
    'api key'            => ['account.apiKey', '1234567890'],
    'postnl cutoff time' => ['carrier.postnl.cutoffTime', '17:00'],
]);

it('updates settings', function () {
    /** @var \MyParcelNL\Pdk\Settings\Repository\MockSettingsRepository $repository */
    $repository        = Pdk::get(SettingsRepositoryInterface::class);
    $createSettingsKey = Pdk::get('createSettingsKey');

    $newLabelSettings = new LabelSettings([
        LabelSettings::DESCRIPTION => 'new custom text',
    ]);

    $repository->storeSettings($newLabelSettings);

    $storedLabelSettings = new LabelSettings($repository->get($createSettingsKey(LabelSettings::ID)));
    expect($storedLabelSettings->description)->toBe('new custom text');
});

it('gets a single setting through the settings facade', function () {
    $apiKey = Settings::get(AccountSettings::ID . '.' . AccountSettings::API_KEY);

    expect($apiKey)->toBe('1234567890');
});

it('gets a single setting through the settings facade by namespace', function () {
    $apiKey = Settings::get(AccountSettings::API_KEY, AccountSettings::ID);

    expect($apiKey)->toBe('1234567890');
});

it('gets a settings group through the settings facade', function () {
    $account = new AccountSettings(Settings::get(AccountSettings::ID));

    expect($account)
        ->toBeInstanceOf(AccountSettings::class)
        ->and($account->toArray())
        ->toEqual([
            'id'                           => AccountSettings::ID,
            AccountSettings::API_KEY       => '1234567890',
            AccountSettings::API_KEY_VALID => true,
        ]);
});

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Repository;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(
    new UsesMockPdkInstance([
        PdkSettingsRepositoryInterface::class => autowire(MockSettingsRepository::class)->constructor([
            AccountSettings::ID => [
                AccountSettings::API_KEY => '1234567890',
            ],
            CarrierSettings::ID => [
                'postnl' => [
                    CarrierSettings::ALLOW_DELIVERY_OPTIONS => true,
                    CarrierSettings::CUTOFF_TIME            => '17:00',
                ],
                'dhl'    => [
                    CarrierSettings::ALLOW_DELIVERY_OPTIONS => false,
                ],
                'bpost'  => [
                    CarrierSettings::ALLOW_DELIVERY_OPTIONS => true,
                ],
            ],
        ]),
    ])
);

it('retrieves all categories and fields', function () {
    /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $repository */
    $repository = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settings   = $repository->all();

    assertMatchesJsonSnapshot(json_encode($settings->toArrayWithoutNull()));
});

it('retrieves a single setting from a category', function (string $key, $expected) {
    /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $repository */
    $repository        = Pdk::get(PdkSettingsRepositoryInterface::class);
    $createSettingsKey = Pdk::get('createSettingsKey');

    expect($repository->get($createSettingsKey($key)))->toBe($expected);
})->with([
    'api key'            => ['account.apiKey', '1234567890'],
    'postnl cutoff time' => ['carrier.postnl.cutoffTime', '17:00'],
]);

it('updates settings', function () {
    /** @var MockSettingsRepository $repository */
    $repository        = Pdk::get(PdkSettingsRepositoryInterface::class);
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

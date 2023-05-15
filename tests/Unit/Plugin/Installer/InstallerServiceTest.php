<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Plugin\Installer;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Facade\Installer;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function DI\autowire;
use function DI\factory;
use function DI\value;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        'platform' => value(Platform::SENDMYPARCEL_NAME),
        'appInfo'  => factory(function (): AppInfo {
            return new AppInfo([
                'name'    => 'test',
                'version' => '1.2.0',
            ]);
        }),

        MigrationServiceInterface::class => autowire(MockMigrationService::class),

        'defaultSettings' => value([
            CheckoutSettings::ID => [
                // Default value of 'pickupLocationsDefaultView' comes from the Platform .
                CheckoutSettings::DELIVERY_OPTIONS_HEADER => 'default',
            ],
        ]),
    ])
);

it('performs a fresh install of the app, filling default values from platform and config', function () {
    /** @var SettingsRepositoryInterface $settingsRepository */
    $settingsRepository  = Pdk::get(SettingsRepositoryInterface::class);
    $installedVersionKey = Pdk::get('settingKeyInstalledVersion');

    // Remove the installed version from the settings:
    $settingsRepository->store($installedVersionKey, null);

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual(null)
        ->and($settingsRepository->get('checkout.deliveryOptionsHeader'))
        ->toBe(null)
        ->and($settingsRepository->get('checkout.pickupLocationsDefaultView'))
        ->toBe(null);

    Installer::install();

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual('1.2.0')
        ->and($settingsRepository->get('checkout.deliveryOptionsHeader'))
        ->toBe('default')
        ->and($settingsRepository->get('checkout.pickupLocationsDefaultView'))
        ->toBe('map');
});

it('upgrades app to new version', function () {
    /** @var SettingsRepositoryInterface $settingsRepository */
    $settingsRepository  = Pdk::get(SettingsRepositoryInterface::class);
    $installedVersionKey = Pdk::get('settingKeyInstalledVersion');

    // Set the installed version to 1.1.0:
    $settingsRepository->store($installedVersionKey, '1.1.0');

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual('1.1.0')
        ->and($settingsRepository->get('label.description'))
        ->toBe(null)
        ->and($settingsRepository->get('account.apiKey'))
        ->toBe(null);

    Installer::install();

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual('1.2.0')
        // Expect 1.1.0 migration to not have run
        ->and($settingsRepository->get('label.description'))
        ->toBe(null)
        // Expect 1.2.0 migration to have run
        ->and($settingsRepository->get('account.apiKey'))
        ->toBe('new-api-key');
});

it('runs down migrations on uninstall', function () {
    /** @var SettingsRepositoryInterface $settingsRepository */
    $settingsRepository  = Pdk::get(SettingsRepositoryInterface::class);
    $installedVersionKey = Pdk::get('settingKeyInstalledVersion');

    // Set the installed version to 1.1.0:
    $settingsRepository->store($installedVersionKey, '1.1.0');
    $settingsRepository->store('account.apiKey', '12345');

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual('1.1.0');

    Installer::uninstall();

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual(null)
        // Expect 1.1.0 migration to have run
        ->and($settingsRepository->get('label.description'))
        ->toBe('old-description')
        // Expect 1.2.0 migration to not have run
        ->and($settingsRepository->get('account.apiKey'))
        ->toBe('12345');
});

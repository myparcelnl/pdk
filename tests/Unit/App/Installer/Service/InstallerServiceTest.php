<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Service;

use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Installer;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesAccountMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSettingsMock;
use Psr\Log\LoggerInterface;

use function DI\factory;
use function DI\value;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(
    new UsesMockPdkInstance([
        'appInfo'  => factory(function (): AppInfo {
            return new AppInfo([
                'name'    => 'test',
                'version' => '1.3.0',
            ]);
        }),

        'defaultSettings' => value([
            CheckoutSettings::ID => [
                // Default value of 'pickupLocationsDefaultView' comes from the Platform .
                CheckoutSettings::DELIVERY_OPTIONS_HEADER => 'default',
            ],
        ]),
    ]),
    new UsesAccountMock(),
    new UsesSettingsMock()
);

afterEach(function () {
    \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::resetExtraUpgrades();
});

function expectSettingsToContain(array $values): void
{
    /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = Pdk::get(PdkSettingsRepositoryInterface::class);
    $settings           = $settingsRepository->all();

    expect(Arr::dot($settings->toArray()))->toHaveKeysAndValues($values);
}

it('performs a fresh install of the app, filling default values from config', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository  = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey = Pdk::get('settingKeyInstalledVersion');

    // Remove the installed version from the settings:
    $settingsRepository->store($installedVersionKey, null);

    expect($settingsRepository->get($installedVersionKey))->toBe(null);

    Installer::install();

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual('1.3.0');

    expectSettingsToContain([
        /** From default settings */
        'checkout.deliveryOptionsHeader'      => 'default',

        /**
         * Expect installation migration to have run
         *
         * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockInstallationMigration100
         */
        'order.emptyParcelWeight'             => 300,
        'order.emptyMailboxWeight'            => 200,

        /**
         * Expect 1.2.0 migration to not have run (as it's only in the upgrade migrations)
         *
         * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration120
         */
        'order.barcodeInNoteTitle'            => null,
    ]);
});

it('upgrades app to new version', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository  = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey = Pdk::get('settingKeyInstalledVersion');

    // Set the installed version to 1.1.0:
    $settingsRepository->store($installedVersionKey, '1.1.0');
    expect($settingsRepository->get($installedVersionKey))->toEqual('1.1.0');

    Installer::install();

    expect($settingsRepository->get($installedVersionKey))->toEqual('1.3.0');

    expectSettingsToContain([
        /**
         * Expect installation migration not to have run
         *
         * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockInstallationMigration100
         */
        'order.emptyParcelWeight'  => null,

        /**
         * Expect 1.1.0 migration to not have run
         *
         * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration110
         * */
        'label.description'        => null,

        /**
         * Expect 1.2.0 migration to have run
         *
         * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration120
         */
        'order.barcodeInNoteTitle' => 'new-barcode-in-note',

        /**
         * Expect 1.3.0 migration to have been run
         *
         * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration130
         */
        'order.emptyMailboxWeight' => 400,
    ]);
});

it('runs down migrations on uninstall', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository  = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey = Pdk::get('settingKeyInstalledVersion');
    $createSettingsKey   = Pdk::get('createSettingsKey');

    $settingsRepository->store($installedVersionKey, '1.3.0');
    $settingsRepository->store($createSettingsKey('account.apiKey'), '12345');

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual('1.3.0');

    Installer::uninstall();

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual(null);

    expectSettingsToContain([
        /**
         * Expect installation migration to have been reversed
         *
         * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockInstallationMigration100
         * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration130
         */
        'order.emptyParcelWeight'  => 200,
        'order.emptyMailboxWeight' => 100,

        /**
         * Expect 1.1.0 migration to have been reversed
         *
         * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration110
         */
        'label.description'        => 'old-description',

        /**
         * Expect 1.2.0 migration to have been reversed
         *
         * @see \MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration120
         */
        'order.barcodeInNoteTitle' => 'old-barcode-in-note',
    ]);
});

it('passes through arbitrary arguments', function ($_, array $result) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);

    expect($logger->getLogs())->toContain($result);
})->with([
    'install' => [
        'method' => function () {
            Installer::install('appelboom', 12345);
        },
        'result' => [
            'level'   => 'debug',
            'message' => '[PDK]: install arguments',
            'context' => ['appelboom', 12345],
        ],
    ],

    'uninstall' => [
        'method' => function () {
            /** @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
            $settingsRepository  = Pdk::get(PdkSettingsRepositoryInterface::class);
            $installedVersionKey = Pdk::get('settingKeyInstalledVersion');

            $settingsRepository->store($installedVersionKey, '1.3.0');

            Installer::uninstall(12, 'peer');
        },
        'result' => [
            'level'   => 'debug',
            'message' => '[PDK]: uninstall arguments',
            'context' => [12, 'peer'],
        ],
    ],
]);

it('does not install if version is equal', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository  = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey = Pdk::get('settingKeyInstalledVersion');
    $createSettingsKey   = Pdk::get('createSettingsKey');

    $settingsRepository->store($installedVersionKey, '1.3.0');
    $settingsRepository->store($createSettingsKey('label.description'), 'description');

    Installer::install();

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual('1.3.0')
        ->and($settingsRepository->get($createSettingsKey('label.description')))
        ->toBe('description');
});

it('does not uninstall if is not installed', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository  = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey = Pdk::get('settingKeyInstalledVersion');
    $createSettingsKey   = Pdk::get('createSettingsKey');

    // Set the installed version to null:
    $settingsRepository->store($installedVersionKey, null);
    $settingsRepository->store($createSettingsKey('label.description'), 'description');

    Installer::uninstall();

    expect($settingsRepository->get($installedVersionKey))
        ->toEqual(null)
        ->and($settingsRepository->get($createSettingsKey('label.description')))
        ->toBe('description');
});

it('seeds applied_migrations from installed_version on first access', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    // Simulate an existing install upgraded past 1.2.0 but before this PDK change.
    $settingsRepository->store($installedVersionKey, '1.2.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    Installer::install();

    $applied = $settingsRepository->get($appliedMigrationsKey);

    // Mock migrations in the test bootstrap: MockUpgradeMigration110 (1.1.0),
    // MockUpgradeMigration120 (1.2.0), MockUpgradeMigration130 (1.3.0).
    // Versions <= 1.2.0 should be seeded as applied; 1.3.0 should NOT be seeded.
    expect($applied)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration110::class)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration120::class)
        ->not->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration130::class);
});

it('seeds applied_migrations with every upgrade migration after a fresh install', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    // Simulate a fresh install: no installed_version, no applied_migrations.
    $settingsRepository->store($installedVersionKey, null);
    $settingsRepository->store($appliedMigrationsKey, null);

    // Register one timestamped migration to prove it too gets pre-marked.
    \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::addUpgradeMigration(
        \MyParcelNL\Pdk\Tests\Bootstrap\MockTimestampedMigration20260101::class
    );

    Installer::install();

    $applied = $settingsRepository->get($appliedMigrationsKey);

    // Every upgrade migration that was registered at install time must be pre-marked.
    expect($applied)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration110::class)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration120::class)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration130::class)
        ->toContain('2026_01_01_000000_mock_timestamped');

    // The timestamped migration's up() must NOT have run (only pre-marked, not executed).
    // MockTimestampedMigration20260101::up() writes order.mockTimestampedMarker = 'applied'
    // via the Settings facade, so had it run the marker would be 'applied' instead of null.
    expect($settingsRepository->get($appliedMigrationsKey))
        ->not->toBeEmpty()
        ->and($settingsRepository->get('order.mockTimestampedMarker'))
        ->toBeNull();
});

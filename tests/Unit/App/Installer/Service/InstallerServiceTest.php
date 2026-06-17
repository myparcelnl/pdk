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
    // Versions <= 1.2.0 are seeded from installed_version; 1.3.0 was not seeded but
    // runs as an upgrade migration and is recorded by markMigrationApplied() after its up() call.
    expect($applied)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration110::class)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration120::class)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration130::class);

    // Prove the seed boundary at 1.2.0 via side effects, not just list membership:
    // 110 and 120 were SEEDED, so their up() must NOT have run (their settings stay null),
    // while 130 was not seeded and therefore ran (it writes order.emptyMailboxWeight = 400).
    expectSettingsToContain([
        'label.description'        => null, // MockUpgradeMigration110 seeded, not run
        'order.barcodeInNoteTitle' => null, // MockUpgradeMigration120 seeded, not run
        'order.emptyMailboxWeight' => 400,  // MockUpgradeMigration130 not seeded, ran
    ]);
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

it('records a migration identity in applied_migrations after it runs', function () {
    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    // Pre-seed as if on 1.1.0 — only MockUpgradeMigration110 considered applied.
    $settingsRepository->store($installedVersionKey, '1.1.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    Installer::install();

    $applied = $settingsRepository->get($appliedMigrationsKey);

    expect($applied)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration120::class)
        ->toContain(\MyParcelNL\Pdk\Tests\Bootstrap\MockUpgradeMigration130::class);
});

it('loads a file-based migration and runs it exactly once', function () {
    $tmpDir = sys_get_temp_dir() . '/pdk_migration_test_' . uniqid();
    mkdir($tmpDir, 0777, true);
    $file = $tmpDir . '/2026_04_17_100000_test_file_migration.php';

    file_put_contents($file, <<<'PHP'
<?php
use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;

return new class extends AbstractTimestampedMigration {
    public function up(): void
    {
        /** @var PdkSettingsRepositoryInterface $repo */
        $repo = Pdk::get(PdkSettingsRepositoryInterface::class);
        $key  = Pdk::get('createSettingsKey')('order.barcodeInNoteTitle');
        $repo->store($key, 'file-migration-applied');
    }
};
PHP
    );

    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    // Simulate an install that is behind the current version so the upgrade path runs.
    $settingsRepository->store($installedVersionKey, '1.2.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    try {
        \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::addUpgradeMigration($file);

        Installer::install();

        $applied = $settingsRepository->get($appliedMigrationsKey);

        expect($applied)->toContain('2026_04_17_100000_test_file_migration');
        expectSettingsToContain(['order.barcodeInNoteTitle' => 'file-migration-applied']);
    } finally {
        @unlink($file);
        @rmdir($tmpDir);
    }
});

it('auto-discovers timestamped migration files from migrationDirectory', function () {
    $tmpDir = sys_get_temp_dir() . '/pdk_autodiscover_' . uniqid();
    mkdir($tmpDir, 0777, true);

    $file = $tmpDir . '/2026_06_01_000000_autodiscover_test.php';
    file_put_contents($file, <<<'PHP'
<?php
use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;

return new class extends AbstractTimestampedMigration {
    public function up(): void
    {
        /** @var PdkSettingsRepositoryInterface $repo */
        $repo = Pdk::get(PdkSettingsRepositoryInterface::class);
        $repo->store('order.autoDiscoverMarker', 'applied');
    }
};
PHP
    );

    Pdk::set('migrationDirectory', $tmpDir);

    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    // Installed version must differ from current (1.3.0) to trigger the upgrade path.
    $settingsRepository->store($installedVersionKey, '1.2.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    try {
        Installer::install();

        $applied = $settingsRepository->get($appliedMigrationsKey);

        expect($applied)->toContain('2026_06_01_000000_autodiscover_test');
        expect($settingsRepository->get('order.autoDiscoverMarker'))->toBe('applied');
    } finally {
        Pdk::set('migrationDirectory', null);
        @unlink($file);
        @rmdir($tmpDir);
    }
});

it('does not duplicate-run when the same file is both in migrationDirectory and registered via MigrationService', function () {
    $tmpDir = sys_get_temp_dir() . '/pdk_dedupe_' . uniqid();
    mkdir($tmpDir, 0777, true);

    $file = $tmpDir . '/2026_06_02_000000_dedupe_test.php';
    file_put_contents($file, <<<'PHP'
<?php
use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;

return new class extends AbstractTimestampedMigration {
    public function up(): void
    {
        $GLOBALS['__dedupe_runs'] = ($GLOBALS['__dedupe_runs'] ?? 0) + 1;
    }
};
PHP
    );

    Pdk::set('migrationDirectory', $tmpDir);

    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository   = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey  = Pdk::get('settingKeyInstalledVersion');
    $appliedMigrationsKey = Pdk::get('settingKeyAppliedMigrations');

    // Installed version must differ from current (1.3.0) to trigger the upgrade path.
    $settingsRepository->store($installedVersionKey, '1.2.0');
    $settingsRepository->store($appliedMigrationsKey, null);

    // Register the SAME file via the MigrationService too.
    \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::addUpgradeMigration($file);

    $GLOBALS['__dedupe_runs'] = 0;

    try {
        Installer::install();

        expect($GLOBALS['__dedupe_runs'])->toBe(1);
    } finally {
        Pdk::set('migrationDirectory', null);
        unset($GLOBALS['__dedupe_runs']);
        @unlink($file);
        @rmdir($tmpDir);
    }
});

it('runs down() on timestamped file-based migrations during uninstall', function () {
    $tmpDir = sys_get_temp_dir() . '/pdk_migration_down_test_' . uniqid();
    mkdir($tmpDir, 0777, true);
    $file = $tmpDir . '/2026_05_01_000000_down_test_migration.php';

    // Write a migration whose down() marks a sentinel key that no other mock migration touches.
    file_put_contents($file, <<<'PHP'
<?php
use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;

return new class extends AbstractTimestampedMigration {
    public function up(): void
    {
        // No-op: this migration exists only to test that down() is called on uninstall.
    }

    public function down(): void
    {
        /** @var PdkSettingsRepositoryInterface $repo */
        $repo = Pdk::get(PdkSettingsRepositoryInterface::class);
        $key  = Pdk::get('createSettingsKey')('order.downTestMarker');
        $repo->store($key, 'down-ran');
    }
};
PHP
    );

    /** @var PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository  = Pdk::get(PdkSettingsRepositoryInterface::class);
    $installedVersionKey = Pdk::get('settingKeyInstalledVersion');

    try {
        \MyParcelNL\Pdk\Tests\Bootstrap\MockMigrationService::addUpgradeMigration($file);

        // Store an installed version so uninstall() proceeds.
        $settingsRepository->store($installedVersionKey, '1.3.0');

        Installer::uninstall();

        // The sentinel key is not a declared OrderSettings model property, so we read
        // directly via the repository instead of expectSettingsToContain().
        $sentinel = $settingsRepository->get(Pdk::get('createSettingsKey')('order.downTestMarker'));

        expect($sentinel)->toBe('down-ran');
    } finally {
        @unlink($file);
        @rmdir($tmpDir);
    }
});

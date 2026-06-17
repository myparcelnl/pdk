<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Service;

use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\TimestampedMigrationInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings as SettingsFacade;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\Settings;

class InstallerService implements InstallerServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface
     */
    private $migrationService;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    private $settingsRepository;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface $migrationService
     */
    public function __construct(
        PdkSettingsRepositoryInterface $settingsRepository,
        MigrationServiceInterface      $migrationService
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->migrationService   = $migrationService;
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     */
    public function install(...$args): void
    {
        $installedVersion = $this->getInstalledVersion();
        $currentVersion   = Pdk::getAppInfo()->version;

        if ($installedVersion === $currentVersion) {
            return;
        }

        Pdk::clearCache();

        if ($installedVersion) {
            Logger::debug("Migrating from $installedVersion to $currentVersion");
            $this->migrateUp($currentVersion);
        } else {
            Logger::debug("Installing $currentVersion");
            $this->executeInstallation(...$args);
        }

        $this->updateInstalledVersion($currentVersion);
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     */
    public function uninstall(...$args): void
    {
        Pdk::clearCache();

        $installedVersion = $this->getInstalledVersion();

        if ($installedVersion) {
            Logger::debug("Uninstalling $installedVersion");
            $this->executeUninstallation(...$args);
            $this->updateInstalledVersion(null);
        }
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     */
    protected function executeInstallation(...$args): void
    {
        $this->setDefaultSettings();
        $this->migrateInstall();
        $this->seedAppliedMigrationsForFreshInstall();
    }

    /**
     * Pre-marks every registered upgrade migration as applied on a fresh install.
     * This prevents them from firing retroactively on the user's first upgrade —
     * they're considered "baked into" the installed version.
     *
     * @return void
     */
    protected function seedAppliedMigrationsForFreshInstall(): void
    {
        $upgrades = $this->createMigrationCollection(
            method_exists($this->migrationService, 'getUpgradeMigrations')
                ? $this->migrationService->getUpgradeMigrations()
                : $this->migrationService->all()
        );

        $ids = $upgrades
            ->map(function (MigrationInterface $m) {
                return $this->resolveMigrationId($m);
            })
            ->values()
            ->all();

        $this->settingsRepository->store(Pdk::get('settingKeyAppliedMigrations'), $ids);
    }

    /**
     * @param  array $args
     *
     * @return void
     */
    protected function executeUninstallation(...$args): void
    {
        $this->migrateUninstall();
    }

    /**
     * @return null|string
     */
    protected function getInstalledVersion(): ?string
    {
        return $this->settingsRepository->get(Pdk::get('settingKeyInstalledVersion'));
    }

    /**
     * Returns the identity string used to track whether a migration has been applied.
     * Falls back to the class FQCN for class-based migrations that don't implement
     * TimestampedMigrationInterface.
     *
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $migration
     *
     * @return string
     */
    protected function resolveMigrationId(MigrationInterface $migration): string
    {
        if ($migration instanceof TimestampedMigrationInterface) {
            return $migration->getId();
        }

        return get_class($migration);
    }

    /**
     * Returns the list of migration identities already applied on this installation.
     * On first access after a plugin upgrade (when the key doesn't exist yet but
     * installed_version does), seeds the list from the legacy installed_version
     * gate so class-based migrations that already ran are not re-executed.
     *
     * Fresh installs bypass this lazy seed path — executeInstallation() seeds
     * eagerly with ALL registered upgrade migrations. See that method for why.
     *
     * @param  null|\MyParcelNL\Pdk\Base\Support\Collection $allMigrations Required on first access to compute the seed.
     *
     * @return string[]
     */
    protected function getAppliedMigrations(?Collection $allMigrations = null): array
    {
        $key    = Pdk::get('settingKeyAppliedMigrations');
        $stored = $this->settingsRepository->get($key);

        // An empty result is treated as "not yet seeded": the settings repository
        // returns an empty array for a key that was never stored, so it cannot be
        // distinguished from a deliberately-empty list. Re-seeding an empty list is
        // idempotent, so treating empty as absent is safe.
        if (is_array($stored) && ! empty($stored)) {
            return $stored;
        }

        if (null === $allMigrations) {
            // Cannot seed without the collection; return empty but do NOT persist.
            return [];
        }

        $installedVersion = $this->getInstalledVersion();

        if (! $installedVersion) {
            // No installed_version and no applied_migrations. This only happens
            // when getAppliedMigrations is called during install() before
            // executeInstallation completes. Return empty without persisting —
            // executeInstallation will seed eagerly.
            return [];
        }

        // Existing install upgrading to a PDK that has this tracking system.
        // Mark every class-based migration whose version is <= installed_version as applied.
        // Timestamp-based migrations are intentionally NOT seeded — they represent
        // net-new work relative to the pre-tracking era and should run.
        $seed = $allMigrations
            ->filter(function (MigrationInterface $m) use ($installedVersion) {
                if ($m instanceof TimestampedMigrationInterface) {
                    return false;
                }

                return version_compare($m->getVersion(), $installedVersion, '<=');
            })
            ->map(function (MigrationInterface $m) {
                return $this->resolveMigrationId($m);
            })
            ->values()
            ->all();

        $this->settingsRepository->store($key, $seed);

        return $seed;
    }

    /**
     * Appends a migration identity to the persisted applied list.
     *
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $migration
     *
     * @return void
     */
    protected function markMigrationApplied(MigrationInterface $migration): void
    {
        $key     = Pdk::get('settingKeyAppliedMigrations');
        $applied = $this->getAppliedMigrations();
        $id      = $this->resolveMigrationId($migration);

        if (! in_array($id, $applied, true)) {
            $applied[] = $id;
            $this->settingsRepository->store($key, $applied);
        }
    }

    /**
     * @return void
     */
    protected function migrateDown(): void
    {
        $this->runDownMigrations(
            $this->getUpgradeMigrations()
                ->filter(function (MigrationInterface $migration) {
                    return version_compare($migration->getVersion(), $this->getInstalledVersion(), '<=');
                })
        );
    }

    /**
     * @return void
     */
    protected function migrateInstall(): void
    {
        $this->runUpMigrations($this->getInstallationMigrations());
    }

    /**
     * @param  string $version
     *
     * @return void
     */
    protected function migrateUp(string $version): void
    {
        $this->runUpMigrations($this->getUpgradeMigrations($version));
    }

    /**
     * @return void
     */
    protected function setDefaultSettings(): void
    {
        $settings = new Settings(SettingsFacade::getDefaults());

        $this->settingsRepository->storeAllSettings($settings);
    }

    /**
     * @param  null|string $version
     *
     * @return void
     */
    protected function updateInstalledVersion(?string $version): void
    {
        $this->settingsRepository->store(Pdk::get('settingKeyInstalledVersion'), $version);
    }

    /**
     * @template T of \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface
     * @param  array<T> $migrations
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    private function createMigrationCollection(array $migrations): Collection
    {
        return Collection::make($migrations)
            ->map(function (string $className) {
                return Pdk::get($className);
            });
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection<\MyParcelNL\Pdk\App\Installer\Contract\InstallationMigrationInterface>
     * @todo v3.0.0 remove legacy support
     */
    private function getInstallationMigrations(): Collection
    {
        if (! method_exists($this->migrationService, 'getInstallationMigrations')) {
            Logger::deprecated(
                sprintf('Method "%s::all()"', MigrationServiceInterface::class),
                'getUpgradeMigrations and getInstallationMigrations'
            );

            return new Collection();
        }

        return $this->createMigrationCollection($this->migrationService->getInstallationMigrations());
    }

    /**
     * @param  null|string $version
     *
     * @return \MyParcelNL\Pdk\Base\Support\Collection<\MyParcelNL\Pdk\App\Installer\Contract\UpgradeMigrationInterface>
     * @todo v3.0.0 remove legacy support
     */
    private function getUpgradeMigrations(?string $version = null): Collection
    {
        $useLegacy = ! method_exists($this->migrationService, 'getUpgradeMigrations');

        if ($useLegacy) {
            Logger::deprecated(
                sprintf('Method "%s::all()"', MigrationServiceInterface::class),
                'getUpgradeMigrations and getInstallationMigrations'
            );
        }

        $migrations = $useLegacy
            ? $this->migrationService->all()
            : $this->migrationService->getUpgradeMigrations();

        $collection = $this->createMigrationCollection($migrations);

        if (! $version) {
            return $collection;
        }

        // Trigger seeding on first access (pass collection so seed can be computed).
        $applied = $this->getAppliedMigrations($collection);

        return $collection->filter(function (MigrationInterface $migration) use ($applied) {
            return ! in_array($this->resolveMigrationId($migration), $applied, true);
        });
    }

    private function migrateUninstall(): void
    {
        $this->migrateDown();
        $this->runDownMigrations($this->getInstallationMigrations());
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection<MigrationInterface> $migrations
     *
     * @return void
     */
    private function runDownMigrations(Collection $migrations): void
    {
        $migrations
            ->sort(function (MigrationInterface $a, MigrationInterface $b) {
                return version_compare($b->getVersion(), $a->getVersion());
            })
            ->each(function (MigrationInterface $migration) {
                $migration->down();
            });
    }

    /**
     * Sort comparator for migrations, ordering by semantic version.
     *
     * @TODO: extend to place timestamp-based migrations after version-based ones.
     *
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $a
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface $b
     *
     * @return int
     */
    public function compareMigrations(MigrationInterface $a, MigrationInterface $b): int
    {
        return version_compare($a->getVersion(), $b->getVersion());
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Support\Collection<MigrationInterface> $migrations
     *
     * @return void
     */
    private function runUpMigrations(Collection $migrations): void
    {
        $migrations
            ->sort([$this, 'compareMigrations'])
            ->each(function (MigrationInterface $migration) {
                $migration->up();
                $this->markMigrationApplied($migration);
            });
    }
}

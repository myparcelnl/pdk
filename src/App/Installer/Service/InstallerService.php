<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Service;

use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
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

        return $collection->filter(function (MigrationInterface $migration) use ($version) {
            return version_compare($migration->getVersion(), $this->getInstalledVersion(), '>')
                && version_compare($migration->getVersion(), $version, '<=');
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
     * @param  \MyParcelNL\Pdk\Base\Support\Collection<MigrationInterface> $migrations
     *
     * @return void
     */
    private function runUpMigrations(Collection $migrations): void
    {
        $migrations
            ->sort(function (MigrationInterface $a, MigrationInterface $b) {
                return version_compare($a->getVersion(), $b->getVersion());
            })
            ->each(function (MigrationInterface $migration) {
                $migration->up();
            });
    }
}

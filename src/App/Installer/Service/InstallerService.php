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
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\Settings;

class InstallerService implements InstallerServiceInterface
{
    public function __construct(
        private readonly SettingsRepositoryInterface $settingsRepository,
        private readonly MigrationServiceInterface   $migrationService
    ) {
    }

    /**
     * @param  mixed ...$args
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
     */
    public function uninstall(...$args): void
    {
        Pdk::clearCache();

        $installedVersion = $this->getInstalledVersion();

        if ($installedVersion) {
            Logger::debug("Uninstalling $installedVersion");
            $this->executeUninstallation(...$args);
            $this->migrateDown();
            $this->updateInstalledVersion(null);
        }
    }

    protected function executeInstallation(mixed ...$args): void
    {
        $this->setDefaultSettings();
    }

    /**
     * @param  array $args
     */
    protected function executeUninstallation(...$args): void
    {
    }

    protected function getInstalledVersion(): ?string
    {
        return $this->settingsRepository->get(Pdk::get('settingKeyInstalledVersion'));
    }

    protected function migrateDown(): void
    {
        $this->getMigrations()
            ->filter(
                fn(MigrationInterface $migration) => version_compare(
                    $migration->getVersion(),
                    $this->getInstalledVersion(),
                    '<='
                )
            )
            ->each(function (MigrationInterface $migration) {
                $migration->down();
            });
    }

    protected function migrateUp(string $version): void
    {
        $this->getMigrations()
            ->filter(
                fn(MigrationInterface $migration) => version_compare(
                        $migration->getVersion(),
                        $this->getInstalledVersion(),
                        '>'
                    )
                    && version_compare($migration->getVersion(), $version, '<=')
            )
            ->each(function (MigrationInterface $migration) {
                $migration->up();
            });
    }

    protected function setDefaultSettings(): void
    {
        $settings = new Settings(SettingsFacade::getDefaults());

        $this->settingsRepository->storeAllSettings($settings);
    }

    /**
     * @param  null|string $version
     */
    protected function updateInstalledVersion(?string $version): void
    {
        $this->settingsRepository->store(Pdk::get('settingKeyInstalledVersion'), $version);
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection<MigrationInterface>
     */
    private function getMigrations(): Collection
    {
        return Collection::make($this->migrationService->all())
            ->map(fn(string $className) => Pdk::get($className));
    }
}

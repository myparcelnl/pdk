<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Service;

use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings as SettingsFacade;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\Settings;

class InstallerService implements InstallerServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface
     */
    private $migrationService;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface
     */
    private $settingsRepository;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface    $settingsRepository
     * @param  \MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface $migrationService
     */
    public function __construct(
        SettingsRepositoryInterface $settingsRepository,
        MigrationServiceInterface   $migrationService
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

        if (! $installedVersion) {
            $this->executeInstallation(...$args);
        } else {
            $this->migrateUp($currentVersion);
        }

        $this->settingsRepository->store(Pdk::get('settingKeyInstalledVersion'), $currentVersion);
    }

    /**
     * @param  mixed ...$args
     *
     * @return void
     */
    public function uninstall(...$args): void
    {
        $installedVersion = $this->getInstalledVersion();

        if ($installedVersion) {
            $this->executeUninstallation(...$args);
            $this->migrateDown();
            $this->settingsRepository->store(Pdk::get('settingKeyInstalledVersion'), null);
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
    }

    /**
     * @param  array $args
     *
     * @return void
     */
    protected function executeUninstallation(...$args): void
    {
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
        $this->getMigrations()
            ->filter(function (MigrationInterface $migration) {
                return version_compare($migration->getVersion(), $this->getInstalledVersion(), '<=');
            })
            ->each(function (MigrationInterface $migration) {
                $migration->down();
            });
    }

    /**
     * @param  string $version
     *
     * @return void
     */
    protected function migrateUp(string $version): void
    {
        $this->getMigrations()
            ->filter(function (MigrationInterface $migration) use ($version) {
                return version_compare($migration->getVersion(), $this->getInstalledVersion(), '>')
                    && version_compare($migration->getVersion(), $version, '<=');
            })
            ->each(function (MigrationInterface $migration) {
                $migration->up();
            });
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
     * @return \MyParcelNL\Pdk\Base\Support\Collection<MigrationInterface>
     */
    private function getMigrations(): Collection
    {
        return Collection::make($this->migrationService->all())
            ->map(function (string $className) {
                return Pdk::get($className);
            });
    }
}

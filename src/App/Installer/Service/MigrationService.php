<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Service;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;

class MigrationService implements MigrationServiceInterface
{
    /**
     * @return \class-string<\MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface>[]
     * @deprecated use getUpgradeMigrations() instead
     * @todo       remove in v3.0.0
     */
    public function all(): array
    {
        return $this->getUpgradeMigrations();
    }

    /**
     * @return array<class-string<\MyParcelNL\Pdk\App\Installer\Contract\InstallationMigrationInterface>>
     * @todo pull up to interface in v3.0.0
     */
    public function getInstallationMigrations(): array
    {
        return [];
    }

    /**
     * @return array<class-string<\MyParcelNL\Pdk\App\Installer\Contract\UpgradeMigrationInterface>>
     * @todo pull up to interface in v3.0.0
     */
    public function getUpgradeMigrations(): array
    {
        return [];
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Contract;

interface MigrationServiceInterface
{
    /**
     * @return \class-string<\MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface>[]
     * @return array<class-string<\MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface>>
     * @deprecated Will be removed in v3.0.0. Implement getUpgradeMigrations() and getInstallationMigrations() instead
     * @todo       remove in v3.0.0
     */
    public function all(): array;
}

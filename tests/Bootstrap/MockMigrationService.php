<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Service\MigrationService;

class MockMigrationService extends MigrationService
{
    public function getInstallationMigrations(): array
    {
        return [
            MockInstallationMigration100::class,
        ];
    }

    public function getUpgradeMigrations(): array
    {
        return [
            MockUpgradeMigration110::class,
            MockUpgradeMigration120::class,
            MockUpgradeMigration130::class,
        ];
    }
}

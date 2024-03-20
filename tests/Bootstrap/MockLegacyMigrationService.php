<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;

/**
 * @todo remove in v3.0.0
 */
class MockLegacyMigrationService implements MigrationServiceInterface
{
    public function all(): array
    {
        return [
            MockUpgradeMigration110::class,
            MockUpgradeMigration120::class,
            MockUpgradeMigration130::class,
        ];
    }
}

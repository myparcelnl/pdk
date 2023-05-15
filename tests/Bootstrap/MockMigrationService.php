<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;

class MockMigrationService implements MigrationServiceInterface
{
    public function all(): array
    {
        return [
            MockMigration110::class,
            MockMigration120::class,
        ];
    }
}

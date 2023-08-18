<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Service;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Installer\MockMigration110;
use MyParcelNL\Pdk\App\Installer\MockMigration120;

final class MockMigrationService implements MigrationServiceInterface
{
    public function all(): array
    {
        return [
            MockMigration110::class,
            MockMigration120::class,
        ];
    }
}

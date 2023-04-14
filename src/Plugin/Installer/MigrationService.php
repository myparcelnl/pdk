<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Installer;

use MyParcelNL\Pdk\Plugin\Installer\Contract\MigrationServiceInterface;

class MigrationService implements MigrationServiceInterface
{
    public function all(): array
    {
        return [];
    }
}

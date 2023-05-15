<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Contract;

interface MigrationServiceInterface
{
    /**
     * @return array<class-string<\MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface>>
     */
    public function all(): array;
}

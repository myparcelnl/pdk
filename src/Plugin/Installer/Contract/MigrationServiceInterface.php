<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Installer\Contract;

interface MigrationServiceInterface
{
    /**
     * @return array<class-string<\MyParcelNL\Pdk\Plugin\Installer\Contract\MigrationInterface>>
     */
    public function all(): array;
}

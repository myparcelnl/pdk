<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Contract;

/**
 * Migrations that are run during the installation process as well as on a migration after the version in getVersion().
 */
interface InstallationMigrationInterface extends MigrationInterface
{
}

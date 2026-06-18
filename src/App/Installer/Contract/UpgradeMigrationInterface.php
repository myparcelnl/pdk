<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Contract;

/**
 * Migrations that are run during an upgrade.
 *
 * @deprecated Create a timestamped migration instead: a YYYY_MM_DD_HHMMSS_<slug>.php file
 *   returning an anonymous class that extends
 *   {@see \MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration}.
 *   Version-based upgrade migrations do not run reliably on release-candidate builds —
 *   version_compare treats an RC as older than its release, so the migration never fires.
 *   See INT-951.
 */
interface UpgradeMigrationInterface extends MigrationInterface
{
}

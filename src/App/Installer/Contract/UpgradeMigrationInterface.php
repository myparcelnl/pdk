<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Contract;

/**
 * Migrations that are run during an upgrade.
 *
 * @deprecated Create a timestamped migration instead: a YYYY_MM_DD_HHMMSS_<slug>.php file
 *   returning an anonymous class that extends
 *   {@see \MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration}.
 *   A version-based migration requires predicting, up front, the release its getVersion()
 *   should target — and that guess is usually wrong by the time the feature ships (features
 *   slip between releases), so the version rarely matches the release the migration actually
 *   shipped in. Migrations now run by identity rather than version, so that version only
 *   affects ordering, which makes the predicted version misleading. A timestamped migration
 *   needs no such prediction. See INT-951.
 */
interface UpgradeMigrationInterface extends MigrationInterface
{
}

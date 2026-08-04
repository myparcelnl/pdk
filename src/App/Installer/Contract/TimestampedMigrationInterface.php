<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Contract;

interface TimestampedMigrationInterface extends MigrationInterface
{
    /**
     * The migration's stable, unique id: its filename without the .php extension,
     * e.g. "2026_04_17_100000_migrate_carriers". It identifies the migration in the
     * applied-migrations list and sets its run order — because the filename starts with
     * a timestamp, sorting ids alphabetically also sorts the migrations oldest-to-newest.
     */
    public function getId(): string;

    /**
     * Whether the migration ran but did not finish its work.
     *
     * A migration that depends on something outside the shop — an API call, say — can fail for reasons
     * that will clear up on their own. Throwing would abort the upgrade and, because the installer never
     * records a migration that throws, leave the shop retrying a fatal on every load. Reporting failure
     * instead lets the upgrade continue while keeping the migration unrecorded, so it is picked up again
     * next time.
     *
     * @see \MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration::markFailed()
     */
    public function hasFailed(): bool;
}

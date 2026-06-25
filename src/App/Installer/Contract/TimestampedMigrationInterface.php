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
}

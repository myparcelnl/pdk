<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Contract;

interface TimestampedMigrationInterface extends MigrationInterface
{
    /**
     * Stable unique identifier for this migration. Used for per-migration tracking
     * AND for ordering — because filenames follow "YYYY_MM_DD_HHMMSS_<slug>", a
     * lexicographic sort on this string yields chronological order.
     *
     * For file-based migrations this is the filename without extension, e.g.
     * "2026_04_17_100000_migrate_carriers".
     */
    public function getId(): string;
}

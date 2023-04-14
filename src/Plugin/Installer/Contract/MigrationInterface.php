<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Installer\Contract;

interface MigrationInterface
{
    /**
     * Executes actions to reverse the migration.
     */
    public function down(): void;

    /**
     * Returns the version the migration should be executed on.
     */
    public function getVersion(): string;

    /**
     * Executes actions to perform the migration.
     */
    public function up(): void;
}

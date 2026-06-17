<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Migration;

use LogicException;
use MyParcelNL\Pdk\App\Installer\Contract\TimestampedMigrationInterface;

/**
 * Base for file-based, timestamp-named migrations.
 *
 * `up()` is intentionally left abstract: it is the migration's actual work and has
 * no meaningful default, so every concrete migration must implement it — a default
 * would allow a migration that silently does nothing. `down()` defaults to a no-op
 * because most data migrations are one-way; subclasses override it only when a
 * rollback is genuinely possible.
 */
abstract class AbstractTimestampedMigration implements TimestampedMigrationInterface
{
    /** @var string */
    private $id = '';

    /**
     * Called by the InstallerService loader once the migration file has been required.
     * Anonymous-class migrations cannot know their own filename, so identity is injected.
     */
    public function setIdentity(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        if ('' === $this->id) {
            throw new LogicException('Migration identity has not been set. Ensure the migration is loaded via InstallerService::loadFileMigration().');
        }

        return $this->id;
    }

    /**
     * Timestamp-based migrations are not version-gated.
     * This method exists solely to satisfy MigrationInterface.
     */
    final public function getVersion(): string
    {
        throw new LogicException('Timestamp-based migrations are not version-gated. Use getId() for ordering.');
    }

    public function down(): void
    {
        // Default: no-op. Subclasses may override.
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Migration;

use LogicException;
use MyParcelNL\Pdk\App\Installer\Contract\TimestampedMigrationInterface;
use MyParcelNL\Pdk\Facade\Logger;

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

    /** @var bool */
    private $failed = false;

    /**
     * @inheritDoc
     */
    public function hasFailed(): bool
    {
        return $this->failed;
    }

    /**
     * Report that this run did not finish, so the installer leaves the migration unrecorded.
     *
     * Call this instead of throwing when the work could not be completed for a reason that may resolve
     * itself, so the upgrade carries on and the migration is attempted again on the next load. The reason
     * is logged as an error, because a migration that quietly keeps failing is worse than one that fails
     * loudly.
     *
     * @param  string $reason  What could not be done, in terms a reader of the log will understand
     * @param  array  $context Extra detail for the log entry
     */
    protected function markFailed(string $reason, array $context = []): void
    {
        $this->failed = true;

        Logger::error($reason, $context + ['migration' => $this->id]);
    }

    /**
     * Called by the InstallerService loader once the migration file has been required.
     * Anonymous-class migrations cannot know their own filename, so identity is injected.
     */
    public function setIdentity(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
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

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;

/**
 * A migration that runs but reports it could not finish, the way one depending on an API call would.
 *
 * Its id sorts before MockTimestampedMigration20260101, so tests can assert that a migration which fails
 * does not stop the ones after it from running.
 */
final class MockFailingTimestampedMigration extends AbstractTimestampedMigration
{
    public function __construct()
    {
        // In production code, setIdentity is called by the InstallerService loader.
        $this->setIdentity('2025_01_01_000000_mock_failing');
    }

    public function up(): void
    {
        if (isset($GLOBALS['__migration_order'])) {
            $GLOBALS['__migration_order'][] = $this->getId();
        }

        $this->markFailed('Mock migration could not finish.', ['reason' => 'test']);
    }
}

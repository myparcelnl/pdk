<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;

final class MockTimestampedMigration20260101 extends AbstractTimestampedMigration
{
    public function __construct()
    {
        // In production code, setIdentity is called by the InstallerService loader.
        // The mock is registered as a FQCN, not as a file path, so we self-identify here.
        $this->setIdentity('2026_01_01_000000_mock_timestamped');
    }

    public function up(): void
    {
        // Write a sentinel so tests can assert this migration ran.
        /** @var PdkSettingsRepositoryInterface $repo */
        $repo = Pdk::get(PdkSettingsRepositoryInterface::class);
        $repo->store(Pdk::get('createSettingsKey')(OrderSettings::ID . '.mockTimestampedMarker'), 'applied');

        if (isset($GLOBALS['__migration_order'])) {
            $GLOBALS['__migration_order'][] = $this->getId();
        }
    }
}

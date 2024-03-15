<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Contract\InstallationMigrationInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;

class MockInstallationMigration100 implements InstallationMigrationInterface
{
    private const KEY_MAILBOX_WEIGHT = 'order.emptyMailboxWeight';
    private const KEY_PARCEL_WEIGHT  = 'order.emptyParcelWeight';

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface
     */
    private $settingsRepository;

    public function __construct(PdkSettingsRepositoryInterface $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    public function down(): void
    {
        $this->settingsRepository->store(Pdk::get('createSettingsKey')(self::KEY_PARCEL_WEIGHT), 200);
        $this->settingsRepository->store(Pdk::get('createSettingsKey')(self::KEY_MAILBOX_WEIGHT), 100);
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function up(): void
    {
        $this->settingsRepository->store(Pdk::get('createSettingsKey')(self::KEY_PARCEL_WEIGHT), 300);
        $this->settingsRepository->store(Pdk::get('createSettingsKey')(self::KEY_MAILBOX_WEIGHT), 200);
    }
}

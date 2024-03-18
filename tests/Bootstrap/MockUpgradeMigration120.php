<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;

class MockUpgradeMigration120 implements MigrationInterface
{
    private const SETTING_KEY = 'order.barcodeInNoteTitle';

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
        $this->settingsRepository->store($this->getSettingKey(), 'old-barcode-in-note');
    }

    public function getVersion(): string
    {
        return '1.2.0';
    }

    public function up(): void
    {
        $this->settingsRepository->store($this->getSettingKey(), 'new-barcode-in-note');
    }

    private function getSettingKey(): string
    {
        return Pdk::get('createSettingsKey')(self::SETTING_KEY);
    }
}

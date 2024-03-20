<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Contract\UpgradeMigrationInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;

class MockUpgradeMigration110 implements UpgradeMigrationInterface
{
    private const SETTING_KEY = 'label.description';

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
        $this->settingsRepository->store($this->getSettingKey(), 'old-description');
    }

    public function getVersion(): string
    {
        return '1.1.0';
    }

    public function up(): void
    {
        $this->settingsRepository->store($this->getSettingKey(), 'new-description');
    }

    private function getSettingKey(): string
    {
        return Pdk::get('createSettingsKey')(self::SETTING_KEY);
    }
}

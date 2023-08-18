<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

final class MockMigration110 implements MigrationInterface
{
    private const SETTING_KEY = LabelSettings::ID . '.' . LabelSettings::DESCRIPTION;

    /**
     * @var \MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface
     */
    private $settingsRepository;

    public function __construct(SettingsRepositoryInterface $settingsRepository)
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

    /**
     * @return string
     */
    private function getSettingKey(): string
    {
        return Pdk::get('createSettingsKey')(self::SETTING_KEY);
    }
}

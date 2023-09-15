<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

class MockMigration110 implements MigrationInterface
{
    private const SETTING_KEY = LabelSettings::ID . '.' . LabelSettings::DESCRIPTION;

    public function __construct(private readonly SettingsRepositoryInterface $settingsRepository)
    {
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

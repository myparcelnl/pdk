<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Plugin\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

class MockMigration110 implements MigrationInterface
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
        $this->settingsRepository->store(self::SETTING_KEY, 'old-description');
    }

    public function getVersion(): string
    {
        return '1.1.0';
    }

    public function up(): void
    {
        $this->settingsRepository->store(self::SETTING_KEY, 'new-description');
    }
}

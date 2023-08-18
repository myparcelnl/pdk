<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;

final class MockMigration120 implements MigrationInterface
{
    private const SETTING_KEY = AccountSettings::ID . '.' . AccountSettings::API_KEY;

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
        $this->settingsRepository->store(self::SETTING_KEY, 'old-api-key');
    }

    public function getVersion(): string
    {
        return '1.2.0';
    }

    public function up(): void
    {
        $this->settingsRepository->store(self::SETTING_KEY, 'new-api-key');
    }
}

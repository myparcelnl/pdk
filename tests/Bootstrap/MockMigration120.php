<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Installer\Contract\MigrationInterface;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;

class MockMigration120 implements MigrationInterface
{
    private const SETTING_KEY = AccountSettings::ID . '.' . AccountSettings::API_KEY;

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

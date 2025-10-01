<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $environment
 * @property bool $orderMode
 * @property bool $hasCarrierContract
 * @property bool $hasCarrierSmallPackageContract
 */
class AccountGeneralSettings extends Model
{
    public $attributes = [
        'environment'                    => 'production',
        'orderMode'                      => false,
        'hasCarrierContract'             => false,
        'hasCarrierSmallPackageContract' => false,
    ];

    public $casts      = [
        'environment'                    => 'string',
        'orderMode'                      => 'bool',
        'hasCarrierContract'             => 'bool',
        'hasCarrierSmallPackageContract' => 'bool',
    ];

    /**
     * Get the current environment
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        // Check if we're connected to acceptance environment via database
        try {
            $settingsRepository = \MyParcelNL\Pdk\Facade\Pdk::get(\MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface::class);
            $accountSettings = $settingsRepository->all()->account;
            
            if ($accountSettings && $accountSettings->environment) {
                return $accountSettings->environment;
            }
        } catch (\Throwable $e) {
            // Fall back to default behavior if settings can't be loaded
        }

        return $this->environment ?? \MyParcelNL\Pdk\Base\Config::ENVIRONMENT_PRODUCTION;
    }

    /**
     * Set the environment
     *
     * @param  string $environment
     *
     * @return void
     */
    public function setEnvironment(string $environment): void
    {
        $this->environment = $environment;
    }

    /**
     * Get the isTest value, determining it dynamically based on the API environment
     * This method is kept for backward compatibility
     *
     * @return bool
     */
    public function getIsTestAttribute(): bool
    {
        return $this->getEnvironment() !== \MyParcelNL\Pdk\Base\Config::ENVIRONMENT_PRODUCTION;
    }

    /**
     * Check if currently in acceptance environment
     *
     * @return bool
     */
    public function isAcceptance(): bool
    {
        return $this->getEnvironment() === \MyParcelNL\Pdk\Base\Config::ENVIRONMENT_ACCEPTANCE;
    }

    /**
     * Check if currently in production environment
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->getEnvironment() === \MyParcelNL\Pdk\Base\Config::ENVIRONMENT_PRODUCTION;
    }
}

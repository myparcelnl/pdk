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
        // Check if we're connected to acceptance environment via cache file (backward compatibility)
        $cacheFile = sys_get_temp_dir() . \MyParcelNL\Pdk\Base\Config::ACCEPTANCE_CACHE_FILE;
        if (file_exists($cacheFile)) {
            return 'acceptance';
        }

        return $this->environment ?? 'production';
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
        return $this->getEnvironment() !== 'production';
    }

    /**
     * Check if currently in acceptance environment
     *
     * @return bool
     */
    public function isAcceptance(): bool
    {
        return $this->getEnvironment() === 'acceptance';
    }

    /**
     * Check if currently in production environment
     *
     * @return bool
     */
    public function isProduction(): bool
    {
        return $this->getEnvironment() === 'production';
    }
}

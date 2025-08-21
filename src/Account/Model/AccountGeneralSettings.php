<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property bool $isTest
 * @property bool $orderMode
 * @property bool $hasCarrierContract
 * @property bool $hasCarrierSmallPackageContract
 */
class AccountGeneralSettings extends Model
{
    public $attributes = [
        'isTest'                         => null,
        'orderMode'                      => false,
        'hasCarrierContract'             => false,
        'hasCarrierSmallPackageContract' => false,
    ];

    public $casts      = [
        'isTest'                         => 'bool',
        'orderMode'                      => 'bool',
        'hasCarrierContract'             => 'bool',
        'hasCarrierSmallPackageContract' => 'bool',
    ];

    /**
     * Get the isTest value, determining it dynamically based on the API environment
     *
     * @return bool
     */
    public function getIsTestAttribute(): bool
    {
        // Check if we're connected to acceptance environment
        $cacheFile = sys_get_temp_dir() . \MyParcelNL\Pdk\Base\Config::ACCEPTANCE_CACHE_FILE;
        return file_exists($cacheFile);
    }
}

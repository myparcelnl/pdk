<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Settings\Repository\PdkSettingsRepository;
use MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;

final class MockPdkSettingsRepository extends PdkSettingsRepository
{
    /**
     * @param  array                                                  $settings
     * @param  \MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface $cache
     * @param  \MyParcelNL\Pdk\Storage\MemoryCacheStorageDriver       $storage
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct(array $settings = [], CacheStorageInterface $cache, StorageDriverInterface $storage)
    {
        parent::__construct($cache, $storage);

        foreach ($settings as $key => $value) {
            $this->save($key, $value);
        }
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Sdk\src\Support\Arr;

class MockPdkConfig
{
    public const DEFAULT_CONFIG = [
        'api'      => MockApiService::class,
        'config'   => MockConfig::class,
        'settings' => MockPluginSettings::class,
        'storage'  => [
            'default' => MockStorage::class,
        ],
        'logger'   => [
            'default' => MockLogger::class,
        ],
        'service'  => [
            'language' => MockLanguageService::class,
        ],
    ];

    /**
     * @param  array $config
     *
     * @return array
     */
    public static function create(array $config = []): array
    {
        $newConfig = self::DEFAULT_CONFIG;

        foreach (Arr::dot($config) as $item => $value) {
            Arr::set($newConfig, $item, $value);
        }

        return $newConfig;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

class MockPdkConfig
{
    public const DEFAULT_CONFIG = [
        'api'     => MockApiService::class,
        'config'  => MockConfig::class,
        'settings'=> MockSettings::class,
        'storage' => [
            'default' => MockStorage::class,
        ],
        'logger'  => [
            'default' => MockLogger::class,
        ],
    ];
}

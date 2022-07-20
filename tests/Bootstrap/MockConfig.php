<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

class MockConfig
{
    public const DEFAULT_CONFIG = [
        'api'     => MockApiService::class,
        'storage' => [
            'default' => MockStorage::class,
        ],
        'logger'  => [
            'default' => MockLogger::class,
        ],
    ];
}

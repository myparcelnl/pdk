<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

class Config
{
    public static function provideDefaultPdkConfig(): array
    {
        return [
            'api'     => MockApiService::class,
            'storage' => [
                'default' => MockStorage::class,
            ],
            'logger'  => [
                'default' => MockLogger::class,
            ],
        ];
    }
}

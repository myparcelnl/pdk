<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Api\MyParcelApiService;

class Config
{
    public static function provideDefaultPdkConfig(): array
    {
        return [
            'api'     => new MockApiService(),
            'storage' => [
                'default' => new MockStorage(),
            ],
        ];
    }
}

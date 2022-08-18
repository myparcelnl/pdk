<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\ConfigInterface;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Storage\StorageInterface;
use MyParcelNL\Sdk\src\Support\Arr;
use Psr\Log\LoggerInterface;
use function DI\autowire;

class MockPdkConfig
{
    /**
     * @param  array $config
     *
     * @return array
     */
    public static function create(array $config = []): array
    {
        return array_replace_recursive(self::getDefaultConfig(), Arr::dot($config));
    }

    /**
     * @return array
     */
    private static function getDefaultConfig(): array
    {
        return Arr::dot([
            ApiServiceInterface::class      => autowire(MockApiService::class),
            ConfigInterface::class          => autowire(MockConfig::class),
            LanguageServiceInterface::class => autowire(MockLanguageService::class),
            LoggerInterface::class          => autowire(MockLogger::class),
            StorageInterface::class         => autowire(MockStorage::class),

            'settings' => autowire(MockPluginSettings::class),
        ]);
    }
}

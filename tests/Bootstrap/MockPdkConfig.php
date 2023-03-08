<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\ConfigInterface;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Plugin\Action\EndpointActionsInterface;
use MyParcelNL\Pdk\Plugin\Service\ViewServiceInterface;
use MyParcelNL\Pdk\Product\Repository\AbstractProductRepository;
use MyParcelNL\Pdk\Settings\Repository\AbstractSettingsRepository;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Pdk\Storage\StorageInterface;
use MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\factory;
use function DI\value;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
        return [
            'appInfo' => factory(function () {
                return new AppInfo([
                    'name'    => 'pest',
                    'title'   => 'Pest',
                    'version' => '1.0.0',
                    'path'    => 'APP_PATH',
                    'url'     => 'APP_URL',
                ]);
            }),

            'platform' => value(Platform::MYPARCEL_NAME),

            ApiServiceInterface::class        => autowire(MockApiService::class),
            ClientAdapterInterface::class     => autowire(Guzzle7ClientAdapter::class),
            ConfigInterface::class            => autowire(MockConfig::class),
            EndpointActionsInterface::class   => autowire(MockEndpointActions::class),
            LanguageServiceInterface::class   => autowire(MockLanguageService::class),
            LoggerInterface::class            => autowire(MockLogger::class),
            StorageInterface::class           => autowire(MemoryCacheStorage::class),
            AbstractSettingsRepository::class => autowire(MockSettingsRepository::class),
            AbstractProductRepository::class  => autowire(MockProductRepository::class),
            ViewServiceInterface::class       => autowire(MockViewService::class),
        ];
    }
}

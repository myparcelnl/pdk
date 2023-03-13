<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Account\Repository\AccountRepositoryInterface;
use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\ConfigInterface;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\Backend\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\Frontend\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\TaxServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\ViewServiceInterface;
use MyParcelNL\Pdk\Product\Repository\ProductRepositoryInterface;
use MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface;
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

            AccountRepositoryInterface::class       => autowire(MockAccountRepository::class),
            ApiServiceInterface::class              => autowire(MockApiService::class),
            BackendEndpointServiceInterface::class  => autowire(MockBackendEndpointService::class),
            ClientAdapterInterface::class           => autowire(Guzzle7ClientAdapter::class),
            ConfigInterface::class                  => autowire(MockConfig::class),
            FrontendEndpointServiceInterface::class => autowire(MockFrontendEndpointService::class),
            LanguageServiceInterface::class         => autowire(MockLanguageService::class),
            LoggerInterface::class                  => autowire(MockLogger::class),
            OrderStatusServiceInterface::class      => autowire(MockOrderStatusService::class),
            ProductRepositoryInterface::class       => autowire(MockProductRepository::class),
            SettingsRepositoryInterface::class      => autowire(MockSettingsRepository::class),
            StorageInterface::class                 => autowire(MemoryCacheStorage::class),
            TaxServiceInterface::class              => autowire(MockTaxService::class),
            ViewServiceInterface::class             => autowire(MockViewService::class),
        ];
    }
}

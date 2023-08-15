<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
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
        return array_replace(self::getDefaultConfig(), $config);
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

            ApiServiceInterface::class                  => autowire(MockApiService::class),
            BackendEndpointServiceInterface::class      => autowire(MockBackendEndpointService::class),
            ClientAdapterInterface::class               => autowire(Guzzle7ClientAdapter::class),
            ConfigInterface::class                      => autowire(MockConfig::class),
            CronServiceInterface::class                 => autowire(MockCronService::class),
            FileSystemInterface::class                  => autowire(MockFileSystem::class),
            FrontendEndpointServiceInterface::class     => autowire(MockFrontendEndpointService::class),
            LanguageServiceInterface::class             => autowire(MockLanguageService::class),
            LoggerInterface::class                      => autowire(MockLogger::class),
            OrderStatusServiceInterface::class          => autowire(MockOrderStatusService::class),
            PdkAccountRepositoryInterface::class        => autowire(MockPdkAccountRepository::class),
            PdkCartRepositoryInterface::class           => autowire(MockPdkCartRepository::class),
            PdkInterface::class                         => autowire(MockPdk::class),
            PdkOrderRepositoryInterface::class          => autowire(MockPdkOrderRepository::class),
            PdkProductRepositoryInterface::class        => autowire(MockPdkProductRepository::class),
            PdkShippingMethodRepositoryInterface::class => autowire(MockPdkShippingMethodRepository::class),
            PdkWebhooksRepositoryInterface::class       => autowire(MockPdkWebhooksRepository::class),
            SettingsRepositoryInterface::class          => autowire(MockSettingsRepository::class),
            StorageInterface::class                     => autowire(MemoryCacheStorage::class),
            TaxServiceInterface::class                  => autowire(MockTaxService::class),
            ViewServiceInterface::class                 => autowire(MockViewService::class),
        ];
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Account\Platform;
use MyParcelNL\Pdk\Api\Adapter\Guzzle7ClientAdapter;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Service\MockApiService;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Account\Repository\MockPdkAccountRepository;
use MyParcelNL\Pdk\App\Api\Backend\MockBackendEndpointService;
use MyParcelNL\Pdk\App\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\Frontend\MockFrontendEndpointService;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\App\Cart\Repository\MockPdkCartRepository;
use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Installer\Service\MockInstallerService;
use MyParcelNL\Pdk\App\Installer\Service\MockMigrationService;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Repository\MockPdkOrderRepository;
use MyParcelNL\Pdk\App\Order\Repository\MockPdkProductRepository;
use MyParcelNL\Pdk\App\Order\Service\MockOrderStatusService;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Repository\MockPdkShippingMethodRepository;
use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\App\Tax\Service\MockTaxService;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\App\Webhook\Repository\MockPdkWebhooksRepository;
use MyParcelNL\Pdk\App\Webhook\Service\MockPdkWebhookService;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\MockConfig;
use MyParcelNL\Pdk\Base\MockFileSystem;
use MyParcelNL\Pdk\Base\MockPdk;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Base\Service\MockCronService;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Frontend\Service\MockViewService;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Language\Service\MockLanguageService;
use MyParcelNL\Pdk\Logger\MockLogger;
use MyParcelNL\Pdk\Notification\Contract\NotificationServiceInterface;
use MyParcelNL\Pdk\Notification\Service\MockNotificationService;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Repository\MockSettingsRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Pdk\Tests\Bootstrap\Contract\MockPdkServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\Service\MockPdkService;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\factory;
use function DI\value;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class MockPdkConfig
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

            MockPdkServiceInterface::class => autowire(MockPdkService::class),

            ApiServiceInterface::class                  => autowire(MockApiService::class),
            BackendEndpointServiceInterface::class      => autowire(MockBackendEndpointService::class),
            ClientAdapterInterface::class               => autowire(Guzzle7ClientAdapter::class),
            ConfigInterface::class                      => autowire(MockConfig::class),
            CronServiceInterface::class                 => autowire(MockCronService::class),
            FileSystemInterface::class                  => autowire(MockFileSystem::class),
            FrontendEndpointServiceInterface::class     => autowire(MockFrontendEndpointService::class),
            InstallerServiceInterface::class            => autowire(MockInstallerService::class),
            LanguageServiceInterface::class             => autowire(MockLanguageService::class),
            LoggerInterface::class                      => autowire(MockLogger::class),
            MigrationServiceInterface::class            => autowire(MockMigrationService::class),
            NotificationServiceInterface::class         => autowire(MockNotificationService::class),
            OrderStatusServiceInterface::class          => autowire(MockOrderStatusService::class),
            PdkAccountRepositoryInterface::class        => autowire(MockPdkAccountRepository::class),
            PdkCartRepositoryInterface::class           => autowire(MockPdkCartRepository::class),
            PdkInterface::class                         => autowire(MockPdk::class),
            PdkOrderRepositoryInterface::class          => autowire(MockPdkOrderRepository::class),
            PdkProductRepositoryInterface::class        => autowire(MockPdkProductRepository::class),
            PdkShippingMethodRepositoryInterface::class => autowire(MockPdkShippingMethodRepository::class),
            PdkWebhookServiceInterface::class           => autowire(MockPdkWebhookService::class),
            PdkWebhooksRepositoryInterface::class       => autowire(MockPdkWebhooksRepository::class),
            SettingsRepositoryInterface::class          => autowire(MockSettingsRepository::class),
            StorageInterface::class                     => autowire(MemoryCacheStorage::class),
            TaxServiceInterface::class                  => autowire(MockTaxService::class),
            ViewServiceInterface::class                 => autowire(MockViewService::class),
        ];
    }
}

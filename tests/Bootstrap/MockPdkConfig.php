<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\Contract\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface;
use MyParcelNL\Pdk\App\Cart\Contract\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkProductRepositoryInterface;
use MyParcelNL\Pdk\App\ShippingMethod\Contract\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\App\Tax\Contract\TaxServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Audit\Contract\AuditServiceInterface;
use MyParcelNL\Pdk\Audit\Contract\PdkAuditRepositoryInterface;
use MyParcelNL\Pdk\Audit\Service\AuditService;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Frontend\Contract\ViewServiceInterface;
use MyParcelNL\Pdk\Language\Contract\LanguageServiceInterface;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Pdk\Tests\Api\Guzzle7ClientAdapter;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use Psr\Log\LoggerInterface;
use function DI\factory;
use function DI\get;

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
                    'path'    => '/app/.tmp/',
                    'url'     => 'APP_URL',
                ]);
            }),

            ApiServiceInterface::class                  => get(MockApiService::class),
            AuditServiceInterface::class                => get(AuditService::class),
            PdkAuditRepositoryInterface::class          => get(MockPdkAuditRepository::class),
            BackendEndpointServiceInterface::class      => get(MockBackendEndpointService::class),
            CarrierSchema::class                        => get(MockCarrierSchema::class),
            ClientAdapterInterface::class               => get(Guzzle7ClientAdapter::class),
            ConfigInterface::class                      => get(MockConfig::class),
            CronServiceInterface::class                 => get(MockCronService::class),
            FileSystemInterface::class                  => get(MockFileSystem::class),
            FrontendEndpointServiceInterface::class     => get(MockFrontendEndpointService::class),
            InstallerServiceInterface::class            => get(MockInstallerService::class),
            LanguageServiceInterface::class             => get(MockLanguageService::class),
            /**
             * @todo v3.0.0 use PdkLoggerInterface. Leave it for now to test backwards compatibility. :)
             */
            LoggerInterface::class                      => get(MockLogger::class),
            MigrationServiceInterface::class            => get(MockMigrationService::class),
            OrderStatusServiceInterface::class          => get(MockOrderStatusService::class),
            PdkAccountRepositoryInterface::class        => get(MockPdkAccountRepository::class),
            PdkActionsServiceInterface::class           => get(MockPdkActionsService::class),
            PdkCartRepositoryInterface::class           => get(MockPdkCartRepository::class),
            PdkInterface::class                         => get(MockPdk::class),
            PdkOrderNoteRepositoryInterface::class      => get(MockPdkOrderNoteRepository::class),
            PdkOrderRepositoryInterface::class          => get(MockPdkOrderRepository::class),
            PdkProductRepositoryInterface::class        => get(MockPdkProductRepository::class),
            PdkShippingMethodRepositoryInterface::class => get(MockPdkShippingMethodRepository::class),
            PdkWebhookManagerInterface::class           => get(MockPdkWebhookManager::class),
            PdkWebhookServiceInterface::class           => get(MockPdkWebhookService::class),
            PdkWebhooksRepositoryInterface::class       => get(MockPdkWebhooksRepository::class),
            PdkSettingsRepositoryInterface::class       => get(MockSettingsRepository::class),
            StorageInterface::class                     => get(MockMemoryCacheStorage::class),
            TaxServiceInterface::class                  => get(MockTaxService::class),
            ViewServiceInterface::class                 => get(MockViewService::class),

            MemoryCacheStorage::class => get(MockMemoryCacheStorage::class),
        ];
    }
}

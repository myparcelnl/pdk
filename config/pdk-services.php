<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Contract\AccountRepositoryInterface;
use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Account\Service\AccountSettingsService;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Service\CartCalculationService;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsFeesServiceInterface;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\App\DeliveryOptions\Service\DeliveryOptionsFeesService;
use MyParcelNL\Pdk\App\DeliveryOptions\Service\DeliveryOptionsService;
use MyParcelNL\Pdk\App\DeliveryOptions\Service\ShipmentOptionsService;
use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Installer\Service\InstallerService;
use MyParcelNL\Pdk\App\Installer\Service\MigrationService;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\PdkWebhookManager;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Contract\LoggerInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\FileSystem;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Service\CurrencyService;
use MyParcelNL\Pdk\Base\Service\WeightService;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
use MyParcelNL\Pdk\Carrier\Repository\CarrierRepository;
use MyParcelNL\Pdk\Context\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Context\Service\ContextService;
use MyParcelNL\Pdk\Frontend\Contract\FrontendRenderServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ScriptServiceInterface;
use MyParcelNL\Pdk\Frontend\Service\FrontendRenderService;
use MyParcelNL\Pdk\Frontend\Service\ScriptService;
use MyParcelNL\Pdk\Notification\Contract\NotificationServiceInterface;
use MyParcelNL\Pdk\Notification\Service\NotificationService;
use MyParcelNL\Pdk\Platform\PlatformManager;
use MyParcelNL\Pdk\Platform\PlatformManagerInterface;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsManagerInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\SettingsManager;
use MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface;
use MyParcelNL\Pdk\Shipment\Service\DropOffService;
use MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorageDriver;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use function DI\autowire;
use function MyParcelNL\Pdk\linkDeprecatedInterface;

/**
 * Pre-defined services.
 */
return [
    /**
     * The main entry point for the PDK DI container.
     */
    PdkInterface::class                        => autowire(Pdk::class),

    /**
     * Handles account settings.
     */
    AccountSettingsServiceInterface::class     => autowire(AccountSettingsService::class),

    /**
     * Used to make requests to the MyParcel API.
     */
    ApiServiceInterface::class                 => autowire(MyParcelApiService::class),

    /**
     * Retrieves carriers from the config.
     */
    CarrierRepositoryInterface::class          => autowire(CarrierRepository::class),

    /**
     * Does calculations on carts.
     */
    CartCalculationServiceInterface::class     => autowire(CartCalculationService::class),

    /**
     * Reads config files.
     */
    ConfigInterface::class                     => autowire(Config::class),

    /**
     * Provides context for the admin frontend.
     */
    ContextServiceInterface::class             => autowire(ContextService::class),

    /**
     * Provides country codes and logic.
     */
    CountryServiceInterface::class             => autowire(CountryService::class),

    /**
     * Does calculations on currencies.
     */
    CurrencyServiceInterface::class            => autowire(CurrencyService::class),

    /**
     * Handles delivery options fees.
     */
    DeliveryOptionsFeesServiceInterface::class => autowire(DeliveryOptionsFeesService::class),

    /**
     * Handles delivery options configuration.
     */
    DeliveryOptionsServiceInterface::class     => autowire(DeliveryOptionsService::class),

    /**
     * Calculates drop off moments.
     */
    DropOffServiceInterface::class             => autowire(DropOffService::class),

    /**
     * Handles file system operations.
     */
    FileSystemInterface::class                 => autowire(FileSystem::class),

    /**
     * Used to render parts of the admin frontend.
     */
    FrontendRenderServiceInterface::class      => autowire(FrontendRenderService::class),

    /**
     * Handles installation.
     */
    InstallerServiceInterface::class           => autowire(InstallerService::class),

    /**
     * Handles migrations.
     */
    MigrationServiceInterface::class           => autowire(MigrationService::class),

    /**
     * Handles notifications.
     */
    NotificationServiceInterface::class        => autowire(NotificationService::class),

    /**
     * Handles platform specific logic.
     */
    PlatformManagerInterface::class            => autowire(PlatformManager::class),

    /**
     * Handles CDN urls.
     */
    ScriptServiceInterface::class              => autowire(ScriptService::class),

    /**
     * Handles retrieving settings.
     */
    SettingsManagerInterface::class            => autowire(SettingsManager::class),

    /**
     * Calculates shipment options from defaults and product settings.
     */
    ShipmentOptionsServiceInterface::class     => autowire(ShipmentOptionsService::class),

    /**
     * Default storage driver for all repositories. Defaults to in-memory storage. Should be replaced with a proper storage driver.
     *
     * @todo remove default in v3.0.0
     */
    StorageDriverInterface::class              => autowire(MemoryCacheStorageDriver::class),

    /**
     * Cache storage driver for all repositories. Defaults to in-memory storage. Can be replaced with a proper cache driver.
     */
    CacheStorageInterface::class               => autowire(MemoryCacheStorageDriver::class),

    /**
     * Handles weight calculations and unit conversions.
     */
    WeightServiceInterface::class              => autowire(WeightService::class),

    /**
     * @todo remove in v3.0.0
     */
    PdkAccountRepositoryInterface::class       => linkDeprecatedInterface(
        AccountRepositoryInterface::class,
        PdkAccountRepositoryInterface::class
    ),

    /**
     * Handles executing webhooks.
     */
    PdkWebhookManagerInterface::class          => autowire(PdkWebhookManager::class),

    /**
     * @todo remove in v3.0.0
     */
    PdkSettingsRepositoryInterface::class      => linkDeprecatedInterface(
        SettingsRepositoryInterface::class,
        PdkSettingsRepositoryInterface::class
    ),

    /**
     * @todo remove in v3.0.0
     */
    PsrLoggerInterface::class                  => linkDeprecatedInterface(
        PsrLoggerInterface::class,
        LoggerInterface::class
    ),
];

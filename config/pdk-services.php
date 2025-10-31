<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Contract\AccountRepositoryInterface;
use MyParcelNL\Pdk\Account\Contract\AccountSettingsServiceInterface;
use MyParcelNL\Pdk\Account\Service\AccountSettingsService;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\App\Api\Contract\PdkActionsServiceInterface;
use MyParcelNL\Pdk\App\Api\Service\PdkActionsService;
use MyParcelNL\Pdk\App\Cart\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\App\Cart\Service\CartCalculationService;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsFeesServiceInterface;
use MyParcelNL\Pdk\App\DeliveryOptions\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\App\DeliveryOptions\Service\DeliveryOptionsFeesService;
use MyParcelNL\Pdk\App\DeliveryOptions\Service\DeliveryOptionsService;
use MyParcelNL\Pdk\App\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\App\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\App\Installer\Service\InstallerService;
use MyParcelNL\Pdk\App\Installer\Service\MigrationService;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderOptionsServiceInterface;
use MyParcelNL\Pdk\App\Order\Service\PdkOrderOptionsService;
use MyParcelNL\Pdk\App\Service\DeliveryOptionsResetService;
use MyParcelNL\Pdk\App\Webhook\Contract\PdkWebhookManagerInterface;
use MyParcelNL\Pdk\App\Webhook\PdkWebhookManager;
use MyParcelNL\Pdk\Audit\Contract\AuditServiceInterface;
use MyParcelNL\Pdk\Audit\Service\AuditService;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Contract\ZipServiceInterface;
use MyParcelNL\Pdk\Base\FileSystem;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Service\CurrencyService;
use MyParcelNL\Pdk\Base\Service\WeightService;
use MyParcelNL\Pdk\Base\Service\ZipService;
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
use MyParcelNL\Pdk\Proposition\PropositionManager;
use MyParcelNL\Pdk\Proposition\PropositionManagerInterface;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsManagerInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\SettingsManager;
use MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface;
use MyParcelNL\Pdk\Shipment\Service\DropOffService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;

use function DI\autowire;
use function DI\factory;

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
     * Used to manage audit data.
     */
    AuditServiceInterface::class               => autowire(AuditService::class),

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
     * Handles platform specific logic. (LEGACY - deprecated)
     *
     * @deprecated Use PropositionManagerInterface instead
     */
    PlatformManagerInterface::class            => autowire(PlatformManager::class),

    /**
     * Handles proposition specific logic. (NEW - preferred)
     */
    PropositionManagerInterface::class         => autowire(PropositionManager::class),

    /**
     * Handles CDN urls.
     */
    ScriptServiceInterface::class              => autowire(ScriptService::class),

    /**
     * Handles retrieving settings.
     */
    SettingsManagerInterface::class            => autowire(SettingsManager::class),

    /**
     * Default storage driver for all repositories. Defaults to in-memory storage. Can be replaced with a proper cache driver.
     */
    StorageInterface::class                    => autowire(MemoryCacheStorage::class),

    /**
     * Handles weight calculations and unit conversions.
     */
    WeightServiceInterface::class              => autowire(WeightService::class),

    /**
     * Handles tri-state values
     */
    TriStateServiceInterface::class            => autowire(TriStateService::class),

    /**
     * Addresses microservice proxy
     */
    AddressesApiService::class                 => autowire(),

    /**
     * @todo remove in v3.0.0
     */
    PdkAccountRepositoryInterface::class       => factory(function () {
        return \MyParcelNL\Pdk\Facade\Pdk::get(AccountRepositoryInterface::class);
    }),

    /**
     * @todo remove in v3.0.0
     */
    PdkSettingsRepositoryInterface::class      => factory(function () {
        return \MyParcelNL\Pdk\Facade\Pdk::get(SettingsRepositoryInterface::class);
    }),

    /**
     * Handles executing pdk actions.
     */
    PdkActionsServiceInterface::class          => autowire(PdkActionsService::class),

    /**
     * Handles order options calculation.
     */
    PdkOrderOptionsServiceInterface::class     => autowire(PdkOrderOptionsService::class),

    /**
     * Handles resetting delivery options when delivery options are disabled.
     */
    DeliveryOptionsResetService::class         => autowire(DeliveryOptionsResetService::class),

    /**
     * Handles executing webhooks.
     */
    PdkWebhookManagerInterface::class          => autowire(PdkWebhookManager::class),

    /**
     * Handles zipping files.
     */
    ZipServiceInterface::class                 => autowire(ZipService::class),
];

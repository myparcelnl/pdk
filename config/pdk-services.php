<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Contract\AccountRepositoryInterface;
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
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Service\CurrencyService;
use MyParcelNL\Pdk\Base\Service\WeightService;
use MyParcelNL\Pdk\Context\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Context\Service\ContextService;
use MyParcelNL\Pdk\Frontend\Contract\FrontendRenderServiceInterface;
use MyParcelNL\Pdk\Frontend\Contract\ScriptServiceInterface;
use MyParcelNL\Pdk\Frontend\Service\FrontendRenderService;
use MyParcelNL\Pdk\Frontend\Service\ScriptService;
use MyParcelNL\Pdk\Platform\PlatformManager;
use MyParcelNL\Pdk\Platform\PlatformManagerInterface;
use MyParcelNL\Pdk\Settings\Contract\SettingsManagerInterface;
use MyParcelNL\Pdk\Settings\SettingsManager;
use MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface;
use MyParcelNL\Pdk\Shipment\Service\DropOffService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
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
     * Used to make requests to the MyParcel API.
     */
    ApiServiceInterface::class                 => autowire(MyParcelApiService::class),

    /**
     * Does calculations on carts.
     */
    CartCalculationServiceInterface::class     => autowire(CartCalculationService::class),

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
     * Default storage driver for all repositories. Defaults to in-memory storage. Can be replaced with a proper cache driver.
     */
    StorageInterface::class                    => autowire(MemoryCacheStorage::class),

    /**
     * Handles weight calculations and unit conversions.
     */
    WeightServiceInterface::class              => autowire(WeightService::class),

    /**
     * @todo remove in v3.0.0
     */
    PdkAccountRepositoryInterface::class       => factory(function () {
        return \MyParcelNL\Pdk\Facade\Pdk::get(AccountRepositoryInterface::class);
    }),
];

<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\Base\Contract\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Contract\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Contract\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Service\CurrencyService;
use MyParcelNL\Pdk\Base\Service\WeightService;
use MyParcelNL\Pdk\Frontend\Contract\ScriptServiceInterface;
use MyParcelNL\Pdk\Frontend\Service\ScriptService;
use MyParcelNL\Pdk\Plugin\Contract\CartCalculationServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\ContextServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\FrontendRenderServiceInterface;
use MyParcelNL\Pdk\Plugin\Contract\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\Plugin\Installer\Contract\InstallerServiceInterface;
use MyParcelNL\Pdk\Plugin\Installer\Contract\MigrationServiceInterface;
use MyParcelNL\Pdk\Plugin\Installer\InstallerService;
use MyParcelNL\Pdk\Plugin\Installer\MigrationService;
use MyParcelNL\Pdk\Plugin\Service\CartCalculationService;
use MyParcelNL\Pdk\Plugin\Service\ContextService;
use MyParcelNL\Pdk\Plugin\Service\DeliveryOptionsService;
use MyParcelNL\Pdk\Plugin\Service\FrontendRenderService;
use MyParcelNL\Pdk\Plugin\Service\ShipmentOptionsService;
use MyParcelNL\Pdk\Settings\Contract\SettingsManagerInterface;
use MyParcelNL\Pdk\Settings\SettingsManager;
use MyParcelNL\Pdk\Shipment\Contract\DropOffServiceInterface;
use MyParcelNL\Pdk\Shipment\Service\DropOffService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use function DI\autowire;

/**
 * Pre-defined services.
 */
return [
    /**
     * Used to make requests to the MyParcel API.
     */
    ApiServiceInterface::class             => autowire(MyParcelApiService::class),

    /**
     * Default storage driver for all repositories. Defaults to in-memory storage. Can be replaced with a proper cache driver.
     */
    StorageInterface::class                => autowire(MemoryCacheStorage::class),

    /**
     * Used to render parts of the admin frontend.
     */
    FrontendRenderServiceInterface::class  => autowire(FrontendRenderService::class),

    /**
     * Provides context for the admin frontend.
     */
    ContextServiceInterface::class         => autowire(ContextService::class),

    /**
     * Handles CDN urls.
     */
    ScriptServiceInterface::class          => autowire(ScriptService::class),

    /**
     * Provides country codes and logic.
     */
    CountryServiceInterface::class         => autowire(CountryService::class),

    /**
     * Does calculations on currencies.
     */
    CurrencyServiceInterface::class        => autowire(CurrencyService::class),

    /**
     * Handles weight calculations and unit conversions.
     */
    WeightServiceInterface::class          => autowire(WeightService::class),

    /**
     * Does calculations on carts.
     */
    CartCalculationServiceInterface::class => autowire(CartCalculationService::class),

    /**
     * Handles delivery options configuration.
     */
    DeliveryOptionsServiceInterface::class => autowire(DeliveryOptionsService::class),

    /**
     * Calculates drop off moments.
     */
    DropOffServiceInterface::class         => autowire(DropOffService::class),

    /**
     * Calculates shipment options from defaults and product settings.
     */
    ShipmentOptionsServiceInterface::class => autowire(ShipmentOptionsService::class),

    /**
     * Handles migrations.
     */
    MigrationServiceInterface::class       => autowire(MigrationService::class),

    /**
     * Handles installation.
     */
    InstallerServiceInterface::class       => autowire(InstallerService::class),

    /**
     * Handles retrieving settings.
     */
    SettingsManagerInterface::class        => autowire(SettingsManager::class),
];

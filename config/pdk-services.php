<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\Base\Concern\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Concern\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Base\Service\CountryServiceInterface;
use MyParcelNL\Pdk\Base\Service\CurrencyService;
use MyParcelNL\Pdk\Base\Service\WeightService;
use MyParcelNL\Pdk\Frontend\Service\ScriptService;
use MyParcelNL\Pdk\Frontend\Service\ScriptServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\CartCalculationService;
use MyParcelNL\Pdk\Plugin\Service\CartCalculationServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\ContextService;
use MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\DeliveryOptionsService;
use MyParcelNL\Pdk\Plugin\Service\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\RenderService;
use MyParcelNL\Pdk\Plugin\Service\RenderServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\ShipmentOptionsService;
use MyParcelNL\Pdk\Plugin\Service\ShipmentOptionsServiceInterface;
use MyParcelNL\Pdk\Shipment\Service\DropOffService;
use MyParcelNL\Pdk\Shipment\Service\DropOffServiceInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Pdk\Storage\StorageInterface;
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
    RenderServiceInterface::class          => autowire(RenderService::class),

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
];

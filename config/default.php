<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Repository\AccountRepositoryInterface;
use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\Base\Concern\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Concern\WeightServiceInterface;
use MyParcelNL\Pdk\Base\CronServiceInterface;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Base\Service\CurrencyService;
use MyParcelNL\Pdk\Base\Service\WeightService;
use MyParcelNL\Pdk\Frontend\Service\ScriptService;
use MyParcelNL\Pdk\Frontend\Service\ScriptServiceInterface;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\Backend\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Api\Frontend\FrontendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Repository\PdkCartRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Repository\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Repository\PdkShippingMethodRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Service\ContextService;
use MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\DeliveryOptionsServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\OrderStatusServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\RenderService;
use MyParcelNL\Pdk\Plugin\Service\RenderServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\ViewServiceInterface;
use MyParcelNL\Pdk\Plugin\Webhook\PdkWebhookServiceInterface;
use MyParcelNL\Pdk\Plugin\Webhook\Repository\PdkWebhooksRepositoryInterface;
use MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Shipment\Service\DropOffService;
use MyParcelNL\Pdk\Shipment\Service\DropOffServiceInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Pdk\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\env;
use function DI\value;

return [
    /**
     * Information about the app that is using the PDK.
     */
    'appInfo'   => value([
        'name'    => null,
        'title'   => null,
        'path'    => null,
        'url'     => null,
        'version' => null,
    ]),

    /**
     * User agent to pass to requests.
     */
    'userAgent' => value([]),

    'apiUrl' => env('PDK_API_URL', 'https://api.myparcel.nl'),

    'mode' => env('PDK_MODE', Pdk::MODE_PRODUCTION),

    'rootDir'    => value(__DIR__ . '/../'),

    /**
     * CDN URL to use for frontend dependencies.
     */
    'baseCdnUrl' => 'https://cdnjs.cloudflare.com/ajax/libs/:name/:version/:filename',

    ApiServiceInterface::class      => autowire(MyParcelApiService::class),
    ContextServiceInterface::class  => autowire(ContextService::class),
    CurrencyServiceInterface::class => autowire(CurrencyService::class),
    DropOffServiceInterface::class  => autowire(DropOffService::class),
    RenderServiceInterface::class   => autowire(RenderService::class),
    ScriptServiceInterface::class   => autowire(ScriptService::class),
    StorageInterface::class         => autowire(MemoryCacheStorage::class),
    WeightServiceInterface::class   => autowire(WeightService::class),

    AccountRepositoryInterface::class           => autowire(),
    ClientAdapterInterface::class               => autowire(),
    CronServiceInterface::class                 => autowire(),
    DeliveryOptionsServiceInterface::class      => autowire(),
    LanguageServiceInterface::class             => autowire(),
    LoggerInterface::class                      => autowire(),
    OrderStatusServiceInterface::class          => autowire(),
    PdkCartRepositoryInterface::class           => autowire(),
    PdkOrderRepositoryInterface::class          => autowire(),
    PdkShippingMethodRepositoryInterface::class => autowire(),
    PdkWebhookServiceInterface::class           => autowire(),
    PdkWebhooksRepositoryInterface::class       => autowire(),
    SettingsRepositoryInterface::class          => autowire(),
    ViewServiceInterface::class                 => autowire(),

    FrontendEndpointServiceInterface::class => autowire(),
    BackendEndpointServiceInterface::class  => autowire(),
];

<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Plugin\Action\EndpointActionsInterface;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Plugin\Service\ContextService;
use MyParcelNL\Pdk\Plugin\Service\ContextServiceInterface;
use MyParcelNL\Pdk\Plugin\Service\RenderService;
use MyParcelNL\Pdk\Plugin\Service\RenderServiceInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Pdk\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\env;
use function DI\value;

return [
    'mode'            => env('PDK_MODE', Pdk::MODE_PRODUCTION),
    'development_api' => env('DEV_API', 'api.dev.myparcel.nl'),
    'production_api'  => env('PROD_API', Pdk::DEFAULT_API_URL),
    'rootDir'         => value(__DIR__ . '/../'),

    AbstractPdkOrderRepository::class => autowire(),
    ApiServiceInterface::class        => autowire(MyParcelApiService::class),
    ClientAdapterInterface::class     => autowire(),
    ContextServiceInterface::class    => autowire(ContextService::class),
    EndpointActionsInterface::class   => autowire(),
    LanguageServiceInterface::class   => autowire(),
    LoggerInterface::class            => autowire(),
    RenderServiceInterface::class     => autowire(RenderService::class),
    StorageInterface::class           => autowire(MemoryCacheStorage::class),
];

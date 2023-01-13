<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Adapter\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\Base\Concern\CurrencyServiceInterface;
use MyParcelNL\Pdk\Base\Concern\WeightServiceInterface;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Base\Service\CurrencyService;
use MyParcelNL\Pdk\Base\Service\WeightService;
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
    'apiUrl'  => env('PDK_API_URL', 'https://api.myparcel.nl'),
    'mode'    => env('PDK_MODE', Pdk::MODE_PRODUCTION),
    'rootDir' => value(__DIR__ . '/../'),

    ApiServiceInterface::class      => autowire(MyParcelApiService::class),
    ContextServiceInterface::class  => autowire(ContextService::class),
    CurrencyServiceInterface::class => autowire(CurrencyService::class),
    RenderServiceInterface::class   => autowire(RenderService::class),
    StorageInterface::class         => autowire(MemoryCacheStorage::class),
    WeightServiceInterface::class   => autowire(WeightService::class),

    AbstractPdkOrderRepository::class => autowire(),
    ClientAdapterInterface::class     => autowire(),
    EndpointActionsInterface::class   => autowire(),
    LanguageServiceInterface::class   => autowire(),
    LoggerInterface::class            => autowire(),
];

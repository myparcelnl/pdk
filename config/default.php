<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\Language\Service\LanguageServiceInterface;
use MyParcelNL\Pdk\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;

return [
    ApiServiceInterface::class      => autowire(MyParcelApiService::class),
    LanguageServiceInterface::class => autowire(),
    LoggerInterface::class          => autowire(),
    StorageInterface::class         => autowire(),
];

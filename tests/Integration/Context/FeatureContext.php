<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Integration\Bootstrap\BehatApiService;
use MyParcelNL\Pdk\Tests\Integration\Bootstrap\BehatClientAdapter;
use MyParcelNL\Pdk\Tests\Integration\Bootstrap\BehatConfig;
use function DI\autowire;

/**
 * Defines application features from the specific context.
 */
final class FeatureContext extends AbstractContext
{
    /**
     * @param  null|string $name
     * @param  array       $data
     * @param  string      $dataName
     *
     * @throws \Exception
     */
    public function __construct(?string $name = null, array $data = [], string $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        putenv('PDK_DISABLE_CACHE=true');

        MockPdkFactory::create([
            ApiServiceInterface::class           => autowire(BehatApiService::class),
            ClientAdapterInterface::class        => autowire(BehatClientAdapter::class),
            ConfigInterface::class               => autowire(BehatConfig::class),
            PdkAccountRepositoryInterface::class => autowire(MockPdkAccountRepository::class)->constructor(null),
        ]);
    }
}

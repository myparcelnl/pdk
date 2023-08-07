<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Integration\Api\Adapter\BehatMyParcelClientAdapter;
use MyParcelNL\Pdk\Tests\Integration\Api\Service\BehatMyParcelApiService;
use MyParcelNL\Pdk\Tests\Integration\Base\BehatConfig;
use function DI\autowire;
use function DI\value;

final class PdkContext extends AbstractContext
{
    private const EXAMPLES_DIR = __DIR__ . '/../Examples';

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
            'configDirs' => value([
                __DIR__ . '/../../../config',
                self::EXAMPLES_DIR,
            ]),

            'behatExamplesDir' => value(self::EXAMPLES_DIR),

            ApiServiceInterface::class           => autowire(BehatMyParcelApiService::class),
            ClientAdapterInterface::class        => autowire(BehatMyParcelClientAdapter::class),
            ConfigInterface::class               => autowire(BehatConfig::class),
            PdkAccountRepositoryInterface::class => autowire(MockPdkAccountRepository::class)->constructor(null),
        ]);
    }
}

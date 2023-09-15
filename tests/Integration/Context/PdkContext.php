<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkAccountRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Integration\Api\Adapter\BehatMyParcelClientAdapter;
use MyParcelNL\Pdk\Tests\Integration\Api\Service\BehatMyParcelApiService;
use MyParcelNL\Pdk\Tests\Integration\Base\BehatConfig;
use function DI\get;
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

            ApiServiceInterface::class           => get(BehatMyParcelApiService::class),
            ClientAdapterInterface::class        => get(BehatMyParcelClientAdapter::class),
            ConfigInterface::class               => get(BehatConfig::class),
            PdkAccountRepositoryInterface::class => get(MockPdkAccountRepository::class),
        ]);
    }

    /**
     * @Given /^I expect my account to have (\d+) shops?$/
     */
    public function iExpectMyAccountToHaveNShops(int $count): void
    {
        $accountRepository = Pdk::get(PdkAccountRepositoryInterface::class);

        expect(
            $accountRepository->getAccount()->shops->count()
        )->toBe($count);
    }

    /**
     * @Given /my account is set up(?: with (\d+) shops?)?/
     */
    public function myAccountIsSetUp(int $shops = 1): void
    {
        TestBootstrapper::hasAccount($this->getValidApiKey(), $shops);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Integration\Context;

use MyParcelNL\Pdk\Api\Adapter\BehatMyParcelClientAdapter;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Service\BehatMyParcelApiService;
use MyParcelNL\Pdk\App\Account\Contract\PdkAccountRepositoryInterface;
use MyParcelNL\Pdk\Base\BehatConfig;
use MyParcelNL\Pdk\Base\Contract\ConfigInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use function DI\autowire;
use function DI\value;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;

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

        mockPdkProperties([
            'configDirs' => value([
                __DIR__ . '/../../../config',
                self::EXAMPLES_DIR,
            ]),

            'behatExamplesDir' => value(self::EXAMPLES_DIR),

            ApiServiceInterface::class    => autowire(BehatMyParcelApiService::class),
            ClientAdapterInterface::class => autowire(BehatMyParcelClientAdapter::class),
            ConfigInterface::class        => autowire(BehatConfig::class),
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

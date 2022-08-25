<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\Account\Repository\CarrierOptionsRepository;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository;
use MyParcelNL\Pdk\Account\Repository\ShopRepository;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetAccountsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetCarrierConfigurationResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetCarrierOptionsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShopsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockRepository;
use MyParcelNL\Sdk\src\Model\Account\Account;
use MyParcelNL\Sdk\src\Model\Account\CarrierConfiguration;
use MyParcelNL\Sdk\src\Model\Account\Shop;

it('gets repositories', function ($response, $repositoryClass, $expected, $method, $args = []) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new $response());

    /** @var \MyParcelNL\Pdk\Base\Repository\AbstractRepository $repository */
    $repository = $pdk->get($repositoryClass);

    expect($repository->{$method}(...array_values($args)))->toBeInstanceOf($expected);
})->with([
    [
        ExampleGetAccountsResponse::class,
        AccountRepository::class,
        Account::class,
        'getAccount',
    ],
    [
        ExampleGetShopsResponse::class,
        ShopRepository::class,
        Shop::class,
        'getShop',
    ],
    [
        ExampleGetCarrierConfigurationResponse::class,
        ShopCarrierConfigurationRepository::class,
        Collection::class,
        'getCarrierConfigurations',
        ['shopId' => 3],
    ],
    [
        ExampleGetCarrierConfigurationResponse::class,
        ShopCarrierConfigurationRepository::class,
        CarrierConfiguration::class,
        'getCarrierConfiguration',
        ['shopId' => 3, 'carrier' => 'postnl'],
    ],
    [
        ExampleGetCarrierOptionsResponse::class,
        CarrierOptionsRepository::class,
        Collection::class,
        'getCarrierOptions',
        ['shopId' => 3],
    ],
]);

it('uses all methods of repository', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get(ApiServiceInterface::class);
    $api->getMock()
        ->append(new ExampleGetShopsResponse());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockRepository $repository */
    $repository = $pdk->get(MockRepository::class);
    $repository->persist();
    $repository->persist();

    expect($repository->getShopWithParameters(3))
        ->toBeInstanceOf(Shop::class);
});

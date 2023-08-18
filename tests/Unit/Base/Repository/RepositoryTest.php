<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\Account\Model\Shop;
use MyParcelNL\Pdk\Account\Model\ShopCarrierConfiguration;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierOptionsRepository;
use MyParcelNL\Pdk\Account\Repository\ShopRepository;
use MyParcelNL\Pdk\Base\Facade\MockApi;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetAccountsResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetCarrierConfigurationResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetCarrierOptionsResponse;
use MyParcelNL\Pdk\Mock\Api\Response\ExampleGetShopsResponse;

it('gets repositories', function ($response, $repositoryClass, $expected, $method, $args = []) {
    MockApi::enqueue(new $response());

    /** @var \MyParcelNL\Pdk\Base\Repository\ApiRepository $repository */
    $repository = Pdk::get($repositoryClass);

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
        ShopCarrierConfiguration::class,
        'getCarrierConfiguration',
        ['shopId' => 3, 'carrier' => 'postnl'],
    ],
    [
        ExampleGetCarrierOptionsResponse::class,
        ShopCarrierOptionsRepository::class,
        Collection::class,
        'getCarrierOptions',
        ['shopId' => 3],
    ],
]);

it('uses all methods of repository', function () {
    MockApi::enqueue(new ExampleGetShopsResponse());

    /** @var \MyParcelNL\Pdk\Base\Repository\MockApiRepository $repository */
    $repository = Pdk::get(MockApiRepository::class);
    $repository->persist();
    $repository->persist();

    expect($repository->getShopWithParameters(3))
        ->toBeInstanceOf(Shop::class);
});

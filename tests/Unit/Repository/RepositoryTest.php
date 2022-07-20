<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\Account\Repository\CarrierOptionsRepository;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository;
use MyParcelNL\Pdk\Account\Repository\ShopRepository;
use MyParcelNL\Pdk\Base\Collection;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Tests\Api\Response\AccountResponse;
use MyParcelNL\Pdk\Tests\Api\Response\CarrierConfigurationResponse;
use MyParcelNL\Pdk\Tests\Api\Response\CarrierOptionsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ShopResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockRepository;
use MyParcelNL\Sdk\src\Model\Account\Account;
use MyParcelNL\Sdk\src\Model\Account\CarrierConfiguration;
use MyParcelNL\Sdk\src\Model\Account\Shop;

it('gets repositories', function ($response, $repositoryClass, $expected, $method, $args = []) {
    $pdk = PdkFactory::create(MockConfig::DEFAULT_CONFIG);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get('api');
    $api->mock->append(new $response());

    /** @var \MyParcelNL\Pdk\Base\Repository\AbstractRepository $repository */
    $repository = $pdk->get($repositoryClass);

    expect($repository->{$method}(...array_values($args)))->toBeInstanceOf($expected);
})->with([
    [
        AccountResponse::class,
        AccountRepository::class,
        Account::class,
        'getAccount',
    ],
    [
        ShopResponse::class,
        ShopRepository::class,
        Shop::class,
        'getShop',
    ],
    [
        CarrierConfigurationResponse::class,
        ShopCarrierConfigurationRepository::class,
        Collection::class,
        'getCarrierConfigurations',
        ['shopId' => 3],
    ],
    [
        CarrierConfigurationResponse::class,
        ShopCarrierConfigurationRepository::class,
        CarrierConfiguration::class,
        'getCarrierConfiguration',
        ['shopId' => 3, 'carrier' => 'postnl'],
    ],
    [
        CarrierOptionsResponse::class,
        CarrierOptionsRepository::class,
        Collection::class,
        'getCarrierOptions',
        ['shopId' => 3],
    ],
]);

it('uses all methods of repository', function () {
    $pdk = PdkFactory::create(MockConfig::DEFAULT_CONFIG);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get('api');
    $api->mock->append(new ShopResponse());

    $repository = new MockRepository($pdk);
    $repository->save();
    $repository->save();

    expect($repository->getShopWithParameters(3))
        ->toBeInstanceOf(Shop::class);
});

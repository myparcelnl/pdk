<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Account\Repository\AccountRepository;
use MyParcelNL\Pdk\Account\Repository\ShopCarrierConfigurationRepository;
use MyParcelNL\Pdk\Account\Repository\ShopRepository;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Shipment\Repository\CarrierOptionsRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\Config;
use MyParcelNL\Pdk\Tests\Bootstrap\MockStorage;
use MyParcelNL\Sdk\src\Exception\ApiException;
use MyParcelNL\Sdk\src\Model\Account\Shop;

it('gets account related repositories', function ($response, $repositoryClass, $method, $args = []) {
    $pdk = PdkFactory::createPdk(Config::provideDefaultPdkConfig());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get('api');
    $api->mock->append(
        new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['data' => $response])
        )
    );

    /** @var \MyParcelNL\Pdk\Repository\AbstractRepository $repository */
    $repository = $pdk->get($repositoryClass);

    expect($repository->{$method}(...array_values($args)))->not->toThrow(Throwable::class);
})->with([
    [
        ['accounts' => [['platform_id' => 3, 'id' => 3, 'shops' => [['id' => 3, 'name' => 'bloemkool']]]]],
        AccountRepository::class,
        'getAccount',
    ],
    [
        ['shops' => [['id' => 3, 'name' => 'creme fraiche']]],
        ShopRepository::class,
        'getShop',
    ],
    [
        [
            'carrier_configurations' => [
                [
                    'carrier'                           => 5,
                    'default_drop_off_point_identifier' => 'abcdefghijklmnopqrstuvwxyz',
                ],
            ],
        ],
        ShopCarrierConfigurationRepository::class,
        'getCarrierConfigurations',
        ['shopId' => 3],
    ],
    [
        [
            'carrier_configurations' => [
                [
                    'carrier'                => 5,
                    'default_drop_off_point' => [
                        'name'          => 'broccoli',
                        'city'          => '',
                        'location_code' => '',
                        'location_name' => '',
                        'number'        => '',
                        'postal_code'   => '',
                        'street'        => '',
                    ],
                ],
            ],
        ],
        ShopCarrierConfigurationRepository::class,
        'getCarrierConfigurations',
        ['shopId' => 3],
    ],
    [
        ['carrier_options' => [['id' => 7, 'carrier' => ['id' => 5], 'enabled' => true, 'optional' => true]]],
        CarrierOptionsRepository::class,
        'getCarrierOptions',
        ['shopId' => 3],
    ],
]);

it('can use methods of repository', function () {
    $pdk = PdkFactory::createPdk(Config::provideDefaultPdkConfig());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get('api');
    $api->mock->append(
        new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['data' => ['shops' => [['id' => 3, 'name' => 'bloemkool']]]])
        )
    );

    /** @var \MyParcelNL\Pdk\Repository\AbstractRepository $repository */
    $repository = $pdk->get(ShopRepository::class);
    expect($repository->getShop())->not->toThrow(Throwable::class)
        ->and($repository->save())->not->toThrow(Throwable::class);
});

it('will not save unchanged object', function () {
    $storage = new MockStorage();
    $storage->set('shop', new Shop(['id' => 3, 'name' => 'bloemkool']));
    $config = array_merge([
        'storage' => [
            'default' => new MockStorage(),
        ],
    ], Config::provideDefaultPdkConfig());

    $pdk        = PdkFactory::createPdk($config);
    $repository = $pdk->get(ShopRepository::class);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get('api');

    $api->mock->append(
        new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['data' => ['shops' => [['id' => 3, 'name' => 'creme fraiche']]]])
        )
    );

    expect($repository->getShop())->not->toThrow(Throwable::class)
        ->and($repository->save())->not->toThrow(Throwable::class)
        /* next line is for coverage: saving an unchanged object should not trigger storage->set */
        ->and($repository->save())->not->toThrow(Throwable::class);
});

it('can handle api errors', function () {
    $pdk = PdkFactory::createPdk(Config::provideDefaultPdkConfig());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api = $pdk->get('api');
    $api->mock->append(
        new Response(
            422,
            ['Content-Type' => 'application/json'],
            json_encode([])
        )
    );

    /** @var \MyParcelNL\Pdk\Account\Repository\ShopRepository $repository */
    $repository = $pdk->get(ShopRepository::class);

    expect(function () use ($repository) {
        $repository->getShop();
    })->toThrow(ApiException::class);
});

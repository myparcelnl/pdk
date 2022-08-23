<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function DI\autowire;

it('get correct headers', function () {
    $pdk = PdkFactory::create(
        MockPdkConfig::create([
            ApiServiceInterface::class => autowire(MyParcelApiService::class)->constructor([
                'userAgent' => 'Prestashop/1.7.8.6',
                'apiKey'    => 'thisistheapikey',
            ]),
        ])
    );

    $api = $pdk->get(ApiServiceInterface::class);
    expect($api->getHeaders())
        ->toBe([
            'authorization' => 'appelboom dGhpc2lzdGhlYXBpa2V5',
            'User-Agent'    => 'Prestashop/1.7.8.6 MyParcel-PDK/1.13.0 php/7.4.30',
        ]);
});

it('get base url', function () {
    $pdk = PdkFactory::create(
        MockPdkConfig::create([
            ApiServiceInterface::class => autowire(MyParcelApiService::class),
        ])
    );

    $api = $pdk->get(ApiServiceInterface::class);
    expect($api->getBaseUrl())
        ->toBe('https://api.myparcel.nl');
});

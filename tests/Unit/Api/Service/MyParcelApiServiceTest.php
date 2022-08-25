<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Composer\InstalledVersions;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Service\MyParcelApiService;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function DI\autowire;

it('gets correct headers', function () {
    $pdk = PdkFactory::create(
        MockPdkConfig::create([
            ApiServiceInterface::class => autowire(MyParcelApiService::class)->constructor([
                'userAgent' => [
                    'Prestashop' => '1.7.8.6',
                ],
            ]),
        ])
    );

    /** @var \MyParcelNL\Pdk\Api\Service\ApiServiceInterface $api */
    $api = $pdk->get(ApiServiceInterface::class);

    expect($api->getHeaders())
        ->toBe([
            'Authorization' => null,
            'User-Agent'    => sprintf(
                'MyParcel-PDK/%s php/7.4.30 Prestashop/1.7.8.6',
                InstalledVersions::getPrettyVersion(Pdk::PACKAGE_NAME)
            ),
        ]);
});

it('gets base url', function () {
    $pdk = PdkFactory::create(
        MockPdkConfig::create([
            ApiServiceInterface::class => autowire(MyParcelApiService::class)->constructor([
                'baseUrl' => 'https://api.baseurl.com',
            ]),
        ])
    );

    $api = $pdk->get(ApiServiceInterface::class);
    expect($api->getBaseUrl())
        ->toBe('https://api.baseurl.com');
});

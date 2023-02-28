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
    PdkFactory::create(
        MockPdkConfig::create([
            ApiServiceInterface::class => autowire(MyParcelApiService::class)->constructor([
                'userAgent' => [
                    'Prestashop' => '1.7.8.6',
                ],
            ]),
        ])
    );

    /** @var \MyParcelNL\Pdk\Api\Service\ApiServiceInterface $api */
    $api = \MyParcelNL\Pdk\Facade\Pdk::get(ApiServiceInterface::class);

    $headers = $api->getHeaders();

    expect($headers)
        ->toHaveKeys(['Authorization', 'User-Agent'])
        ->and($headers['Authorization'])
        ->toBeNull()
        ->and($headers['User-Agent'])
        ->toMatch(
            sprintf(
                '/MyParcelNL-PDK\/%s php\/[0-9.]+ Prestashop\/1.7.8.6/',
                str_replace('.', '\.', InstalledVersions::getPrettyVersion(Pdk::PACKAGE_NAME))
            )
        );
});

it('gets base url', function () {
    PdkFactory::create(
        MockPdkConfig::create([
            ApiServiceInterface::class => autowire(MyParcelApiService::class)->constructor([
                'baseUrl' => 'https://api.baseurl.com',
            ]),
        ])
    );

    $api = \MyParcelNL\Pdk\Facade\Pdk::get(ApiServiceInterface::class);
    expect($api->getBaseUrl())
        ->toBe('https://api.baseurl.com');
});

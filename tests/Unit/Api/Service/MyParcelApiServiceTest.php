<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function DI\autowire;
use function DI\value;

it('gets correct headers', function () {
    $pdk = PdkFactory::create(
        MockPdkConfig::create([
            'userAgent'                => value(['MyParcelNL-Platform' => '2.0.0', 'Platform' => '1.2.3']),
            ApiServiceInterface::class => autowire(MyParcelApiService::class),
        ])
    );

    /** @var \MyParcelNL\Pdk\Api\Service\MyParcelApiService $api */
    $api = $pdk->get(ApiServiceInterface::class);

    $headers = $api->getHeaders();
    expect(array_keys($headers))
        ->toEqual(['Authorization', 'User-Agent'])
        ->and($headers['Authorization'])
        ->toBeNull()
        ->and($headers['User-Agent'])
        ->toMatch(
            '/MyParcelNL-Platform\/2\.0\.0 Platform\/1\.2\.3 MyParcelNL-PDK\/[\d.]+ php\/[\d.]+/'
        );
});

it('gets base url', function () {
    $pdk = PdkFactory::create(
        MockPdkConfig::create([
            'apiUrl'                   => 'https://api.baseurl.com',
            ApiServiceInterface::class => autowire(MyParcelApiService::class),
        ])
    );

    $api = $pdk->get(ApiServiceInterface::class);
    expect($api->getBaseUrl())
        ->toBe('https://api.baseurl.com');
});

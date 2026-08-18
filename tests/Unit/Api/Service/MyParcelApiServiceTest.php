<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Service;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use function DI\get;
use function DI\value;

it('gets correct headers', function () {
    $pdk = MockPdkFactory::create([
        'userAgent'                => value(['MyParcelNL-Platform' => '2.0.0', 'Platform' => '1.2.3']),
        // No feature flags, so these are the only headers the service adds.
        'apiFeatureFlags'          => value([]),
        ApiServiceInterface::class => get(MyParcelApiService::class),
    ]);

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

it('adds the configured feature flags to the headers', function () {
    $pdk = MockPdkFactory::create([
        'apiFeatureFlags'          => value(['x-dmp-no-tracking']),
        ApiServiceInterface::class => get(MyParcelApiService::class),
    ]);

    /** @var \MyParcelNL\Pdk\Api\Service\MyParcelApiService $api */
    $api = $pdk->get(ApiServiceInterface::class);

    $headers = $api->getHeaders();

    expect(array_keys($headers))
        ->toEqual(['Authorization', 'User-Agent', 'x-dmp-no-tracking'])
        ->and($headers['x-dmp-no-tracking'])
        ->toBe('true');
});

it('gets base url', function () {
    $pdk = MockPdkFactory::create([
        'apiUrl'                   => 'https://api.baseurl.com',
        ApiServiceInterface::class => get(MyParcelApiService::class),
    ]);

    $api = $pdk->get(ApiServiceInterface::class);
    expect($api->getBaseUrl())
        ->toBe('https://api.baseurl.com');
});

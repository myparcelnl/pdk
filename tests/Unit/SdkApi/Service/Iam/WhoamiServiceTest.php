<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\Iam;

use Mockery;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\IamApi\Api\DefaultApi;
use MyParcelNL\Sdk\Client\Generated\IamApi\ApiException;
use MyParcelNL\Sdk\Client\Generated\IamApi\Model\WhoamiGet200Response;
use Psr\Log\LogLevel;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

/**
 * Create a partial mock of WhoamiService that intercepts the DefaultApi call.
 */
function makeWhoamiServiceWithMockedApi(WhoamiGet200Response $response): WhoamiService
{
    TestBootstrapper::hasApiKey('test-api-key');

    $mockApi = Mockery::mock(DefaultApi::class);
    $mockApi->shouldReceive('whoamiGet')->andReturn($response);

    $service = Mockery::mock(WhoamiService::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $service->shouldReceive('getWhoami')->andReturnUsing(function () use ($mockApi) {
        return $mockApi->whoamiGet();
    });

    return $service;
}

it('returns a WhoamiGet200Response on success', function () {
    TestBootstrapper::hasApiKey('test-api-key');

    $expectedResponse = new WhoamiGet200Response([
        'account_id' => '12345',
        'shop_ids'   => ['67890'],
        'features'   => ['ORDER_NOTES', 'DIRECT_PRINTING'],
    ]);

    $service = makeWhoamiServiceWithMockedApi($expectedResponse);

    $response = $service->getWhoami();

    expect($response)->toBeInstanceOf(WhoamiGet200Response::class)
        ->and($response->getFeatures())->toBe(['ORDER_NOTES', 'DIRECT_PRINTING']);
});

it('propagates ApiException from the IAM client', function () {
    TestBootstrapper::hasApiKey('test-api-key');

    $service = Mockery::mock(WhoamiService::class)->makePartial();
    $service->shouldReceive('getWhoami')->andThrow(new ApiException('Unauthorized', 401));

    expect(fn() => $service->getWhoami())->toThrow(ApiException::class);
});

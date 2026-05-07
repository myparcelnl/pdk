<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\MockableCapabilitiesService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns capabilities for a given recipient country', function () {
    TestBootstrapper::hasApiKey('test-key');

    $mockService = new MockableCapabilitiesService();
    $mockService->mockHandler->append(new Response(200, [], json_encode([
        'results' => [
            [
                'carrier'       => 'POSTNL',
                'delivery_type' => 'STANDARD',
                'package_type'  => 'PACKAGE',
            ],
        ],
    ])));

    $repository = new CarrierCapabilitiesRepository(
        Pdk::get(StorageInterface::class),
        $mockService
    );

    $result = $repository->getCapabilitiesForRecipientCountry('NL');

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1);
});

it('caches capabilities by recipient country', function () {
    TestBootstrapper::hasApiKey('test-key');

    $mockService = new MockableCapabilitiesService();

    // Only queue ONE response — a second HTTP call would exhaust the mock and throw
    $mockService->mockHandler->append(new Response(200, [], json_encode([
        'results' => [
            [
                'carrier'       => 'POSTNL',
                'delivery_type' => 'STANDARD',
                'package_type'  => 'PACKAGE',
            ],
        ],
    ])));

    $repository = new CarrierCapabilitiesRepository(
        Pdk::get(StorageInterface::class),
        $mockService
    );

    $first  = $repository->getCapabilitiesForRecipientCountry('NL');
    $second = $repository->getCapabilitiesForRecipientCountry('NL');

    // Both calls return the same data
    expect($first)->toBe($second)
        // Only one HTTP request was made — the second call was served from cache
        ->and($mockService->capturedRequests)->toHaveCount(1);
});

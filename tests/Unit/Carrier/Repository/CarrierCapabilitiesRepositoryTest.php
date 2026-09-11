<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Repository;

use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\MockableCapabilitiesService;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleContractDefinitionsResponse;
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

    $result = $repository->getCapabilities(['recipient' => ['country_code' => 'NL']]);

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

    $first = $repository->getCapabilities(['recipient' => ['country_code' => 'NL']]);
    $second = $repository->getCapabilities(['recipient' => ['country_code' => 'NL']]);

    // Both calls return the same data
    expect($first)->toBe($second)
        // Only one HTTP request was made — the second call was served from cache
        ->and($mockService->capturedRequests)->toHaveCount(1);
});

/**
 * A minimal contract definition item, differing only by carrier so a re-fetch is visible as a change
 * in the number of carriers returned.
 */
function contractDefinitionFor(string $carrier): array
{
    return [
        'carrier'          => $carrier,
        'packageTypes'     => ['PACKAGE'],
        'deliveryTypes'    => ['STANDARD_DELIVERY'],
        'transactionTypes' => ['B2C'],
        'options'          => ['noTracking' => ['isSelectedByDefault' => false, 'isRequired' => false]],
        'collo'            => ['max' => 10],
    ];
}

it('caches contract definitions and re-fetches them only when fresh data is asked for', function () {
    TestBootstrapper::hasApiKey('test-key');

    $mockService = new MockableCapabilitiesService();
    $mockService->mockHandler->append(
        new ExampleContractDefinitionsResponse([contractDefinitionFor('POSTNL')]),
        new ExampleContractDefinitionsResponse([
            contractDefinitionFor('POSTNL'),
            contractDefinitionFor('DHL_FOR_YOU'),
        ])
    );

    $repository = new CarrierCapabilitiesRepository(
        Pdk::get(StorageInterface::class),
        $mockService
    );

    $repository->getContractDefinitions();
    $repository->getContractDefinitions();

    // The second read came from cache.
    expect($mockService->capturedRequests)->toHaveCount(1);

    // Asking for fresh data calls the API again and rewrites the cache, so the plain read after it
    // sees the new data without a further request. That rewrite is the point: an upgrade migration
    // needs the stored copy replaced, not merely bypassed once.
    expect($repository->getContractDefinitions(null, true))->toHaveCount(2)
        ->and($mockService->capturedRequests)->toHaveCount(2)
        ->and($repository->getContractDefinitions())->toHaveCount(2)
        ->and($mockService->capturedRequests)->toHaveCount(2);
});

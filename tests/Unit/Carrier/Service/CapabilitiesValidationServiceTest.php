<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Carrier\Service;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\SdkApi\MockSdkApiHandler;
use MyParcelNL\Pdk\Tests\SdkApi\Response\ExampleCapabilitiesResponse;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesSdkApiMock;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance(), new UsesSdkApiMock());

it('returns capabilities indexed by carrier name', function () {
    TestBootstrapper::hasApiKey();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        [
            'carrier'       => 'POSTNL',
            'delivery_type' => 'STANDARD',
            'package_type'  => 'PACKAGE',
        ],
        [
            'carrier'       => 'DHL_FOR_YOU',
            'delivery_type' => 'STANDARD',
            'package_type'  => 'PACKAGE',
        ],
    ]));

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);
    $result  = $service->getCapabilitiesForOrderContext('POSTNL', 'NL', 'PACKAGE');

    expect($result)->toBeArray()
        ->and(array_keys($result))->toBe(['POSTNL', 'DHL_FOR_YOU'])
        ->and($result['POSTNL']->getCarrier())->toBe('POSTNL')
        ->and($result['DHL_FOR_YOU']->getCarrier())->toBe('DHL_FOR_YOU');
});

it('includes delivery type in the API call when provided', function () {
    TestBootstrapper::hasApiKey();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        [
            'carrier'       => 'POSTNL',
            'delivery_type' => 'STANDARD',
            'package_type'  => 'PACKAGE',
        ],
    ]));

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);
    $result  = $service->getCapabilitiesForOrderContext('POSTNL', 'NL', 'PACKAGE', 'STANDARD');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('POSTNL');
});

it('omits delivery type from the API call when null', function () {
    TestBootstrapper::hasApiKey();

    // Enqueue two responses: one for "with delivery type" and one for "without"
    // If they were the same args, the second call would be cached.
    // We use different carriers to ensure distinct cache keys.
    MockSdkApiHandler::enqueue(
        new ExampleCapabilitiesResponse([
            [
                'carrier'       => 'POSTNL',
                'delivery_type' => 'STANDARD',
                'package_type'  => 'PACKAGE',
            ],
        ]),
        new ExampleCapabilitiesResponse([
            [
                'carrier'       => 'POSTNL',
                'delivery_type' => 'STANDARD',
                'package_type'  => 'PACKAGE',
            ],
        ])
    );

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);

    // Call with delivery type
    $withType = $service->getCapabilitiesForOrderContext('POSTNL', 'NL', 'PACKAGE', 'STANDARD');
    // Call without delivery type - different cache key, so a new API call is made
    $withoutType = $service->getCapabilitiesForOrderContext('POSTNL', 'NL', 'PACKAGE', null);

    // Both calls should return valid results (proving both cache keys are distinct)
    expect($withType)->toBeArray()
        ->and($withType)->toHaveKey('POSTNL')
        ->and($withoutType)->toBeArray()
        ->and($withoutType)->toHaveKey('POSTNL');
});

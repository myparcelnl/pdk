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

it('indexes capabilities by carrier name', function () {
    TestBootstrapper::hasApiKey();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        ['carrier' => 'POSTNL'],
        ['carrier' => 'DHL_FOR_YOU'],
    ]));

    /** @var CapabilitiesValidationService $service */
    $service      = Pdk::get(CapabilitiesValidationService::class);
    $capabilities = $service->getRepository()->getCapabilities(['recipient' => ['cc' => 'NL']]);
    $indexed      = $service->indexByCarrier($capabilities);

    expect(array_keys($indexed))->toBe(['POSTNL', 'DHL_FOR_YOU'])
        ->and($indexed['POSTNL']->getCarrier())->toBe('POSTNL');
});

it('fetches capabilities for a package type indexed by carrier', function () {
    TestBootstrapper::hasApiKey();

    MockSdkApiHandler::enqueue(new ExampleCapabilitiesResponse([
        ['carrier' => 'POSTNL'],
    ]));

    /** @var CapabilitiesValidationService $service */
    $service = Pdk::get(CapabilitiesValidationService::class);
    $result  = $service->getCapabilitiesForPackageType('NL', 'PACKAGE');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('POSTNL');
});

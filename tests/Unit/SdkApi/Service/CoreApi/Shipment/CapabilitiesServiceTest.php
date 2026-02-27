<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment;

use Mockery;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostCapabilitiesRequestV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostContractDefinitionsRequestV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesResponsesCapabilitiesV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesResponsesContractDefinitionsV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use Psr\Log\LogLevel;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

function setupCapabilitiesMock()
{
    TestBootstrapper::hasApiKey('test-key');

    // Create a mock response with results
    $mockResponse = new CapabilitiesResponsesCapabilitiesV2(['results' => []]);

    // Create a partial mock - the constructor runs because we set up the API key first
    $mock = Mockery::mock(CapabilitiesService::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    // Mock executeOperationWithErrorHandling to not send a request
    $mock->shouldReceive('executeOperationWithErrorHandling')
        ->andReturn($mockResponse);

    return $mock;
}

function setupContractDefinitionsMock()
{
    TestBootstrapper::hasApiKey('test-key');

    // Create a mock response with items
    $mockResponse = new CapabilitiesResponsesContractDefinitionsV2(['items' => []]);

    // Create a partial mock - the constructor runs because we set up the API key first
    $mock = Mockery::mock(CapabilitiesService::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    // Mock executeOperationWithErrorHandling to not send a request
    $mock->shouldReceive('executeOperationWithErrorHandling')
        ->andReturn($mockResponse);

    return $mock;
}

// Tests for getCapabilities()
it('can be instantiated', function () {
    TestBootstrapper::hasApiKey('valid-key');

    $service = new CapabilitiesService();

    expect($service)->toBeInstanceOf(CapabilitiesService::class);
});

it('getCapabilities accepts parameters array', function () {
    $service = setupCapabilitiesMock();

    $parameters = [
        'carrier'      => 'POSTNL',
        'recipient'    => ['cc' => 'NL', 'postal_code' => '2132WT'],
        'package_type' => 'PACKAGE',
    ];

    $result = $service->getCapabilities($parameters);

    expect($result)->toBeArray();
});

it('getCapabilities rejects incorrect parameters', function () {
    // Don't use setupMocks() - we want to test the actual request building/validation
    TestBootstrapper::hasApiKey('test-key');

    // We can safely throw an unmocked instance here because the OpenAPI SDK should
    // throw an exception before any real API call is made due to parameter validation
    $service = new CapabilitiesService();

    $parameters = [
        'carrier'      => 2, // Invalid carrier type (should be string)
        'recipient'    => ['cc' => 'NL', 'postal_code' => '2132WT'],
        'package_type' => 'PACKAGE',
    ];

    $allowedValuesString = implode("', '", RefCapabilitiesSharedCarrierV2::getAllowableEnumValues());

    // The OpenAPI SDK validates parameters when building the request object
    expect(fn() => $service->getCapabilities($parameters))
        ->toThrow(
            \InvalidArgumentException::class,
            "Invalid value for enum '\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2', must be one of: '$allowedValuesString'"
        );
});

it('logs operation name as "postCapabilities" when calling getCapabilities', function () {
    TestBootstrapper::hasApiKey('test-key');

    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    $service = new CapabilitiesService();

    try {
        $service->getCapabilities([
            'carrier'   => 'makeitfail',
            'recipient' => ['cc' => 'NL'],
        ]);
    } catch (\Throwable $e) {
        // Should fail due to incorrect log, but we check operation name is included in the logs
    }

    $logs = array_merge(
        $logger->getLogs(LogLevel::DEBUG),
        $logger->getLogs(LogLevel::ERROR)
    );

    $hasOperationLog = false;
    foreach ($logs as $log) {
        if (isset($log['context']['operation']) && $log['context']['operation'] === 'postCapabilities') {
            $hasOperationLog = true;
            break;
        }
    }

    expect($hasOperationLog)->toBeTrue();
});

// Tests for getContractDefinitions()
it('getContractDefinitions accepts carrier parameter', function () {
    TestBootstrapper::hasApiKey('valid-key');

    $service = setupContractDefinitionsMock();

    // Test with specific carrier
    expect($service->getContractDefinitions('POSTNL'))->toBeArray();
});

it('getContractDefinitions accepts null carrier to retrieve all definitions', function () {
    TestBootstrapper::hasApiKey('valid-key');

    $service = setupContractDefinitionsMock();

    // Test with null carrier
    expect($service->getContractDefinitions(null))->toBeArray();
});

it('getContractDefinitions rejects unknown carrier names', function () {
    TestBootstrapper::hasApiKey('valid-key');

    // Use the real method to test validation, not the mock which bypasses it
    $service = new CapabilitiesService();
    $allowedValuesString = implode("', '", (new CapabilitiesPostContractDefinitionsRequestV2())->getCarrierAllowableValues());

    // Test with unknown carrier
    expect(fn() => $service->getContractDefinitions('unknown_carrier'))
        ->toThrow(\InvalidArgumentException::class, "Invalid value 'unknown_carrier' for 'carrier', must be one of '$allowedValuesString'");
});

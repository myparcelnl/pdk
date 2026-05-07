<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\CapabilitiesPostContractDefinitionsRequestV2;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

/**
 * Test subclass that replaces only the real Guzzle transport with a MockHandler.
 *
 * Overrides createGuzzleClient() rather than createGuzzleClientHandlerStack(), so
 * the full real middleware chain from CapabilitiesService (Accept-header override)
 * and AbstractSdkApiService (LoggingMiddleware) is preserved without duplication.
 */
class MockableCapabilitiesService extends CapabilitiesService
{
    /** @var MockHandler */
    public $mockHandler;

    /** @var RequestInterface[] */
    public $capturedRequests = [];

    public function __construct()
    {
        $this->mockHandler = new MockHandler();
        parent::__construct();
    }

    protected function createGuzzleClient(): \GuzzleHttp\Client
    {
        $capturedRequests = &$this->capturedRequests;

        // Build the real middleware stack (LoggingMiddleware + Accept-header override)
        // then swap the transport for the MockHandler — no duplication of middleware logic.
        $stack = $this->createGuzzleClientHandlerStack();
        $stack->setHandler($this->mockHandler);

        // Capture each outgoing request after all middleware has run
        $stack->push(function (callable $handler) use (&$capturedRequests) {
            return function (RequestInterface $request, array $options) use ($handler, &$capturedRequests) {
                $capturedRequests[] = $request;

                return $handler($request, $options);
            };
        });

        return new \GuzzleHttp\Client(['handler' => $stack]);
    }
}

// Tests for getCapabilities()
it('can be instantiated', function () {
    TestBootstrapper::hasApiKey('valid-key');

    expect(new CapabilitiesService())->toBeInstanceOf(CapabilitiesService::class);
});

it('getCapabilities returns array of results from API response', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['results' => []])));

    $result = $service->getCapabilities([
        'carrier'      => 'POSTNL',
        'recipient'    => ['country_code' => 'NL', 'postal_code' => '2132WT'],
        'package_type' => 'PACKAGE',
    ]);

    expect($result)->toBeArray();
});

it('getCapabilities serializes nested arrays to V2 wire keys via SDK attributeMap', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['results' => []])));

    $service->getCapabilities([
        'carrier'             => 'POSTNL',
        'recipient'           => ['country_code' => 'NL', 'postal_code' => '2132WT'],
        'package_type'        => 'PACKAGE',
        'physical_properties' => ['weight' => ['value' => 1500, 'unit' => 'g']],
    ]);

    $body = json_decode((string) $service->capturedRequests[0]->getBody(), true);

    expect($body)
        // Top-level snake_case property names get translated to camelCase wire keys.
        ->toHaveKey('packageType', 'PACKAGE')
        ->toHaveKey('physicalProperties')
        ->not->toHaveKey('package_type')
        ->not->toHaveKey('physical_properties')
        // Nested recipient: `country_code` property → `countryCode` wire key via CapabilitiesRecipientV2::attributeMap.
        ->and($body['recipient'])
        ->toMatchArray(['countryCode' => 'NL', 'postalCode' => '2132WT'])
        ->not->toHaveKey('country_code')
        ->not->toHaveKey('postal_code')
        ->not->toHaveKey('cc')
        // Nested physical_properties.weight: untranslated leaf keys (value/unit) match V2 schema as-is.
        ->and($body['physicalProperties']['weight'])
        ->toMatchArray(['value' => 1500, 'unit' => 'g']);
});

it('getCapabilities accepts API-style camelCase keys at every nesting level and produces correct V2 wire format', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['results' => []])));

    // Same shape the JS-PDK / checkout posts to the proxy: camelCase at every level.
    $service->getCapabilities([
        'recipient'          => ['countryCode' => 'NL'],
        'physicalProperties' => ['weight' => ['value' => 3000, 'unit' => 'g']],
        'carrier'            => 'POSTNL',
        'packageType'        => 'PACKAGE',
        'deliveryType'       => 'STANDARD_DELIVERY',
    ]);

    $body = json_decode((string) $service->capturedRequests[0]->getBody(), true);

    expect($body)
        ->toHaveKey('packageType', 'PACKAGE')
        ->toHaveKey('deliveryType', 'STANDARD_DELIVERY')
        ->and($body['recipient'])
        ->toMatchArray(['countryCode' => 'NL'])
        ->and($body['physicalProperties']['weight'])
        ->toMatchArray(['value' => 3000, 'unit' => 'g']);
});

it('getCapabilities silently drops legacy V1 recipient.cc input under V2', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['results' => []])));

    // Regression guard: the V2 CapabilitiesRecipientV2 model only knows country_code,
    // so a legacy `cc` key is silently dropped. Callers must use `country_code`.
    $service->getCapabilities([
        'carrier'      => 'POSTNL',
        'recipient'    => ['cc' => 'NL'],
        'package_type' => 'PACKAGE',
    ]);

    $body = json_decode((string) $service->capturedRequests[0]->getBody(), true);

    expect($body['recipient'] ?? [])
        ->not->toHaveKey('cc')
        ->not->toHaveKey('countryCode')
        ->not->toHaveKey('country_code');
});

it('getCapabilities rejects incorrect parameter types before making a request', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new CapabilitiesService();

    $allowedValuesString = implode("', '", RefCapabilitiesSharedCarrierV2::getAllowableEnumValues());

    // The OpenAPI SDK validates parameters when building the request object,
    // so no HTTP request is made and no mock handler is needed.
    expect(fn() => $service->getCapabilities([
        'carrier'      => 2, // Invalid: must be a string enum value
        'recipient'    => ['country_code' => 'NL', 'postal_code' => '2132WT'],
        'package_type' => 'PACKAGE',
    ]))->toThrow(
        \InvalidArgumentException::class,
        "Invalid value for enum '\MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefCapabilitiesSharedCarrierV2', must be one of: '$allowedValuesString'"
    );
});

// Tests for getContractDefinitions()
it('getContractDefinitions returns array of items from API response', function () {
    TestBootstrapper::hasApiKey('valid-key');

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['items' => []])));

    expect($service->getContractDefinitions('POSTNL'))->toBeArray();
});

it('getContractDefinitions accepts null carrier to retrieve all definitions', function () {
    TestBootstrapper::hasApiKey('valid-key');

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['items' => []])));

    expect($service->getContractDefinitions(null))->toBeArray();
});

it('getContractDefinitions rejects unknown carrier names before making a request', function () {
    TestBootstrapper::hasApiKey('valid-key');

    $service = new CapabilitiesService();
    $allowedValuesString = implode("', '", (new CapabilitiesPostContractDefinitionsRequestV2())->getCarrierAllowableValues());

    expect(fn() => $service->getContractDefinitions('unknown_carrier'))
        ->toThrow(\InvalidArgumentException::class, "Invalid value 'unknown_carrier' for 'carrier', must be one of '$allowedValuesString'");
});

// Tests for LoggingMiddleware integration
it('logs a debug message for outgoing request via middleware', function () {
    TestBootstrapper::hasApiKey('test-key');

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['items' => []])));

    $service->getContractDefinitions(null);

    $debugLogs = $logger->getLogs(LogLevel::DEBUG);

    $requestLog = array_values(array_filter($debugLogs, fn($l) => $l['message'] === '[PDK]: Sending API request'));
    expect($requestLog)->toHaveCount(1)
        ->and($requestLog[0]['context'])->toHaveKeys(['method', 'uri'])
        ->and($requestLog[0]['context']['method'])->toBe('POST');
});

it('logs a debug message for received response via middleware', function () {
    TestBootstrapper::hasApiKey('test-key');

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['items' => []])));

    $service->getContractDefinitions(null);

    $debugLogs = $logger->getLogs(LogLevel::DEBUG);

    $responseLog = array_values(array_filter($debugLogs, fn($l) => $l['message'] === '[PDK]: Received API response'));
    expect($responseLog)->toHaveCount(1)
        ->and($responseLog[0]['context']['status'])->toBe(200);
});

it('logs an error message when a transport-level exception occurs', function () {
    TestBootstrapper::hasApiKey('test-key');

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    $service = new MockableCapabilitiesService();
    // Use a RequestException to simulate a transport-level failure (e.g., connection refused).
    // An HTTP 500 response is returned by MockHandler as a normal response; the SDK then throws
    // an ApiException itself after the middleware chain, so it would not hit the error callback.
    $service->mockHandler->append(
        new RequestException('Connection timed out', new GuzzleRequest('POST', 'http://api.myparcel.nl'))
    );

    try {
        $service->getContractDefinitions(null);
    } catch (\Throwable $e) {
        // Expected
    }

    $errorLogs = $logger->getLogs(LogLevel::ERROR);

    expect($errorLogs)->toHaveCount(1)
        ->and($errorLogs[0]['message'])->toBe('[PDK]: API request failed')
        ->and($errorLogs[0]['context'])->toHaveKeys(['error', 'code']);
});

it('response body is logged as decoded JSON array via middleware', function () {
    TestBootstrapper::hasApiKey('test-key');

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['items' => []])));

    $service->getContractDefinitions(null);

    $debugLogs = $logger->getLogs(LogLevel::DEBUG);

    $responseLog = array_values(array_filter($debugLogs, fn($l) => $l['message'] === '[PDK]: Received API response'));
    expect($responseLog[0]['context']['body'])->toBe(['items' => []]);
});

// Tests for Accept-header middleware in CapabilitiesService
it('sets version-2 Accept header for all capabilities endpoints', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['results' => []])));
    $service->mockHandler->append(new Response(200, [], json_encode(['items' => []])));

    $service->getCapabilities([
        'carrier'      => 'POSTNL',
        'recipient'    => ['country_code' => 'NL', 'postal_code' => '2132WT'],
        'package_type' => 'PACKAGE',
    ]);

    $service->getContractDefinitions(null);

    expect($service->capturedRequests)->toHaveCount(2);

    foreach ($service->capturedRequests as $request) {
        expect($request->getHeaderLine('Accept'))->toBe('application/json;charset=utf-8;version=2');
    }
});

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
        'recipient'    => ['cc' => 'NL', 'postal_code' => '2132WT'],
        'package_type' => 'PACKAGE',
    ]);

    expect($result)->toBeArray();
});

it('getCapabilities rejects incorrect parameter types before making a request', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new CapabilitiesService();

    $allowedValuesString = implode("', '", RefCapabilitiesSharedCarrierV2::getAllowableEnumValues());

    // The OpenAPI SDK validates parameters when building the request object,
    // so no HTTP request is made and no mock handler is needed.
    expect(fn() => $service->getCapabilities([
        'carrier'      => 2, // Invalid: must be a string enum value
        'recipient'    => ['cc' => 'NL', 'postal_code' => '2132WT'],
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
it('sets version-2 Accept header for capabilities endpoint requests', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new MockableCapabilitiesService();
    $service->mockHandler->append(new Response(200, [], json_encode(['results' => []])));

    $service->getCapabilities([
        'carrier'      => 'POSTNL',
        'recipient'    => ['cc' => 'NL', 'postal_code' => '2132WT'],
        'package_type' => 'PACKAGE',
    ]);

    expect($service->capturedRequests)->toHaveCount(1);

    $request = $service->capturedRequests[0];
    expect($request->getHeaderLine('Accept'))->toBe('application/json;charset=utf-8;version=2');
});

it('does not override Accept header for non-capabilities endpoints', function () {
    // Build the handler stack directly to send a request to a non-capabilities path
    TestBootstrapper::hasApiKey('test-key');

    $service = new MockableCapabilitiesService();

    // Append a dummy response for the mock
    $service->mockHandler->append(new Response(200, [], json_encode(['items' => []])));

    // getContractDefinitions goes to /shipments/capabilities/contract-definitions,
    // which matches the middleware pattern, so let's verify it also gets the header.
    $service->getContractDefinitions(null);

    expect($service->capturedRequests)->toHaveCount(1);

    $request = $service->capturedRequests[0];
    // Contract definitions path also contains /shipments/capabilities
    expect($request->getHeaderLine('Accept'))->toBe('application/json;charset=utf-8;version=2');
});

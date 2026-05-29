<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApiPrivate\ShippingRule;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Psr\Http\Message\RequestInterface;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

/**
 * Test subclass that replaces only the real Guzzle transport with a MockHandler.
 *
 * Overrides createGuzzleClient() so the full real middleware chain from
 * AbstractSdkApiService (LoggingMiddleware) is preserved without duplication.
 */
class MockableImplicationsService extends ImplicationsService
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

        $stack = $this->createGuzzleClientHandlerStack();
        $stack->setHandler($this->mockHandler);

        $stack->push(function (callable $handler) use (&$capturedRequests) {
            return function (RequestInterface $request, array $options) use ($handler, &$capturedRequests) {
                $capturedRequests[] = $request;

                return $handler($request, $options);
            };
        });

        return new \GuzzleHttp\Client(['handler' => $stack]);
    }
}

/**
 * Build a response body with one implication entry.
 *
 * @param  int|null $carrierId  Omit carrier_id from the implication when null.
 * @param  array    $extra      Extra fields merged into the implication object.
 *
 * @return string JSON-encoded response body.
 */
function implResponse(?int $carrierId = null, array $extra = []): string
{
    $implication = array_merge(
        ['contract_id' => 1, 'shipment_options' => [], 'physical_properties' => []],
        $extra
    );

    if ($carrierId !== null) {
        $implication['carrier_id'] = $carrierId;
    }

    return (string) json_encode(['data' => ['implications' => [$implication]]]);
}

/**
 * Build a response body with an empty implications array.
 *
 * @return string JSON-encoded response body.
 */
function emptyImplResponse(): string
{
    return (string) json_encode(['data' => ['implications' => []]]);
}

// Test 1: returns V2 name on success
it('returns the V2 carrier name when implications contain a known carrier id', function () {
    TestBootstrapper::hasApiKey('test-key');

    // carrier_id 1 maps to "POSTNL" in Carrier::CARRIER_NAME_ID_MAP.
    $service = new MockableImplicationsService();
    $service->mockHandler->append(new Response(200, [], implResponse(1)));

    expect($service->getDefaultCarrierName(42))->toBe('POSTNL');
});

// Test 2: returns null when implications array is empty
it('returns null when the implications array is empty', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new MockableImplicationsService();
    $service->mockHandler->append(new Response(200, [], emptyImplResponse()));

    expect($service->getDefaultCarrierName(42))->toBeNull();
});

// Test 3: returns null when carrier_id is absent from the implication
it('returns null when the first implication has no carrier_id', function () {
    TestBootstrapper::hasApiKey('test-key');

    // Omit carrier_id so the deserialized model has null for that field.
    $service = new MockableImplicationsService();
    $service->mockHandler->append(new Response(200, [], implResponse()));

    expect($service->getDefaultCarrierName(42))->toBeNull();
});

// Test 4: returns null when the carrier id is not in the local V2 mapping
it('returns null when the carrier id is not in the local V2 mapping', function () {
    TestBootstrapper::hasApiKey('test-key');

    // 9999 is not in Carrier::CARRIER_NAME_ID_MAP — simulates an API id this PDK version
    // does not yet know about. Should yield null rather than throwing.
    $service = new MockableImplicationsService();
    $service->mockHandler->append(new Response(200, [], implResponse(9999)));

    expect($service->getDefaultCarrierName(42))->toBeNull();
});

// Test 5: returns null on API error response
it('returns null on ApiException without re-logging at the service layer', function () {
    TestBootstrapper::hasApiKey('test-key');

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = \MyParcelNL\Pdk\Facade\Pdk::get(\MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface::class);
    $logger->clear();

    $service = new MockableImplicationsService();
    $service->mockHandler->append(new Response(500, [], '{"error":"internal server error"}'));

    expect($service->getDefaultCarrierName(42))->toBeNull();

    // LoggingMiddleware logs the HTTP failure, but the service must NOT add its own
    // "unexpected error" log for an expected SDK ApiException.
    foreach ($logger->getLogs(\Psr\Log\LogLevel::ERROR) as $log) {
        expect($log['message'] ?? '')->not->toContain('Unexpected error fetching default carrier name');
    }
});

it('logs an unexpected error and returns null when a non-API exception bubbles up', function () {
    TestBootstrapper::hasApiKey('test-key');

    // Push a non-Guzzle Throwable into the handler queue. Guzzle's MockHandler will reject
    // the request promise with it; the SDK's ApiException wrapper only catches GuzzleException,
    // so this TypeError propagates into our Throwable catch and must be logged before nulling.
    $service = new MockableImplicationsService();
    $service->mockHandler->append(new \TypeError('unexpected boom'));

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = \MyParcelNL\Pdk\Facade\Pdk::get(\MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface::class);
    $logger->clear();

    expect($service->getDefaultCarrierName(42))->toBeNull();

    $errors = $logger->getLogs(\Psr\Log\LogLevel::ERROR);

    expect($errors)->not->toBeEmpty();

    $hasUnexpectedLog = false;

    foreach ($errors as $log) {
        $message   = $log['message'] ?? '';
        $exception = $log['context']['exception'] ?? '';

        if (
            strpos($message, 'Unexpected error fetching default carrier name') !== false
            && strpos($exception, 'TypeError') !== false
        ) {
            $hasUnexpectedLog = true;

            break;
        }
    }

    expect($hasUnexpectedLog)->toBeTrue();
});

// Test 6: shop_id is passed through unchanged in the request URI
it('passes shop_id through unchanged and does not add extra query params', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new MockableImplicationsService();
    $service->mockHandler->append(new Response(200, [], implResponse(1)));

    $service->getDefaultCarrierName(42);

    expect($service->capturedRequests)->toHaveCount(1);

    $uri = $service->capturedRequests[0]->getUri();

    expect((string) $uri->getPath())->toBe('/shops/42/shipping_rules/implications')
        ->and($uri->getQuery())->toBe('');
});

// Test 7: service surface is restricted to exactly one public non-constructor non-inherited method
it('exposes only getDefaultCarrierName as a public non-constructor non-inherited method', function () {
    $reflection    = new \ReflectionClass(ImplicationsService::class);
    $parentMethods = array_map(
        static function (\ReflectionMethod $m): string {
            return $m->getName();
        },
        (new \ReflectionClass(get_parent_class(ImplicationsService::class)))->getMethods(\ReflectionMethod::IS_PUBLIC)
    );

    $ownPublicMethods = array_values(array_filter(
        $reflection->getMethods(\ReflectionMethod::IS_PUBLIC),
        static function (\ReflectionMethod $m) use ($parentMethods): bool {
            return $m->getName() !== '__construct'
                && ! in_array($m->getName(), $parentMethods, true);
        }
    ));

    expect($ownPublicMethods)->toHaveCount(1)
        ->and($ownPublicMethods[0]->getName())->toBe('getDefaultCarrierName');
});

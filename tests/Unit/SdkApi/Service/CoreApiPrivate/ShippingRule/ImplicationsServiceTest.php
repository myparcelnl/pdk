<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApiPrivate\ShippingRule;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use Mockery;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Contract\CarrierRepositoryInterface;
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

    public function __construct(CarrierRepositoryInterface $carrierRepository)
    {
        $this->mockHandler = new MockHandler();
        parent::__construct($carrierRepository);
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

    $carrierRepository = Mockery::mock(CarrierRepositoryInterface::class);
    $carrierRepository->shouldReceive('findByLegacyId')
        ->once()
        ->with(1)
        ->andReturn(new Carrier(['carrier' => 'POSTNL']));

    $service = new MockableImplicationsService($carrierRepository);
    $service->mockHandler->append(new Response(200, [], implResponse(1)));

    expect($service->getDefaultCarrierName(42))->toBe('POSTNL');
});

// Test 2: returns null when implications array is empty
it('returns null when the implications array is empty', function () {
    TestBootstrapper::hasApiKey('test-key');

    $carrierRepository = Mockery::mock(CarrierRepositoryInterface::class);
    $carrierRepository->shouldNotReceive('findByLegacyId');

    $service = new MockableImplicationsService($carrierRepository);
    $service->mockHandler->append(new Response(200, [], emptyImplResponse()));

    expect($service->getDefaultCarrierName(42))->toBeNull();
});

// Test 3: returns null when carrier_id is absent from the implication
it('returns null when the first implication has no carrier_id', function () {
    TestBootstrapper::hasApiKey('test-key');

    $carrierRepository = Mockery::mock(CarrierRepositoryInterface::class);
    $carrierRepository->shouldNotReceive('findByLegacyId');

    // Omit carrier_id so the deserialized model has null for that field.
    $service = new MockableImplicationsService($carrierRepository);
    $service->mockHandler->append(new Response(200, [], implResponse()));

    expect($service->getDefaultCarrierName(42))->toBeNull();
});

// Test 4: returns null when carrier id is not found in CarrierRepository
it('returns null when the carrier id is not found in the carrier repository', function () {
    TestBootstrapper::hasApiKey('test-key');

    // Use a valid SDK enum value (2 = BPOST) that is not present in this shop's carriers.
    // findByLegacyId returns null when the carrier is not in the account's carrier list.
    $carrierRepository = Mockery::mock(CarrierRepositoryInterface::class);
    $carrierRepository->shouldReceive('findByLegacyId')
        ->once()
        ->with(2)
        ->andReturn(null);

    $service = new MockableImplicationsService($carrierRepository);
    $service->mockHandler->append(new Response(200, [], implResponse(2)));

    expect($service->getDefaultCarrierName(42))->toBeNull();
});

// Test 5: returns null on API error response
it('returns null on ApiException without re-logging at the service layer', function () {
    TestBootstrapper::hasApiKey('test-key');

    $carrierRepository = Mockery::mock(CarrierRepositoryInterface::class);
    $carrierRepository->shouldNotReceive('findByLegacyId');

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = \MyParcelNL\Pdk\Facade\Pdk::get(\MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface::class);
    $logger->clear();

    $service = new MockableImplicationsService($carrierRepository);
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

    // Force a TypeError out of CarrierRepository to simulate a programmer error somewhere
    // inside the try block. The ApiException catch must NOT swallow this — the Throwable
    // catch must log it before returning null so the bug is surfaced.
    $carrierRepository = Mockery::mock(CarrierRepositoryInterface::class);
    $carrierRepository->shouldReceive('findByLegacyId')
        ->once()
        ->andThrow(new \TypeError('unexpected boom'));

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = \MyParcelNL\Pdk\Facade\Pdk::get(\MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface::class);
    $logger->clear();

    $service = new MockableImplicationsService($carrierRepository);
    $service->mockHandler->append(new Response(200, [], implResponse(1)));

    expect($service->getDefaultCarrierName(42))->toBeNull();

    $errors = $logger->getLogs(\Psr\Log\LogLevel::ERROR);

    expect($errors)->not->toBeEmpty();

    $hasUnexpectedLog = false;

    foreach ($errors as $log) {
        $message   = $log['message'] ?? '';
        $exception = $log['context']['exception'] ?? '';

        if (strpos($message, 'Unexpected error fetching default carrier name') !== false
            && strpos($exception, 'TypeError') !== false) {
            $hasUnexpectedLog = true;

            break;
        }
    }

    expect($hasUnexpectedLog)->toBeTrue();
});

// Test 6: shop_id is passed through unchanged in the request URI
it('passes shop_id through unchanged and does not add extra query params', function () {
    TestBootstrapper::hasApiKey('test-key');

    $carrierRepository = Mockery::mock(CarrierRepositoryInterface::class);
    $carrierRepository->shouldReceive('findByLegacyId')->andReturn(new Carrier(['carrier' => 'POSTNL']));

    $service = new MockableImplicationsService($carrierRepository);
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
        static function (\ReflectionMethod $m): string { return $m->getName(); },
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

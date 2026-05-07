<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Capabilities;

use Mockery;
use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\App\Action\Capabilities\CapabilitiesAction;
use MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException as CoreApiException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

beforeEach(function () {
    TestBootstrapper::hasApiKey('test-api-key');
});

it('handles OPTIONS preflight request', function () {
    $request = Request::create('/', 'OPTIONS');

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('handlePreflightRequest')
        ->with($request)
        ->andReturn(new Response('', 204));

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldNotReceive('getCapabilities');

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(204);
});

it('passes payload to capabilities service and returns results', function () {
    $request = Request::create(
        '/?action=proxyCapabilities',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        '{"carrier":"postnl","country":"NL","package_type":"package"}'
    );

    $expectedPayload = ['carrier' => 'postnl', 'country' => 'NL', 'package_type' => 'package'];
    $fakeResults     = [['carrier' => 'postnl', 'package_types' => ['package']]];

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')
        ->once()
        ->with($expectedPayload)
        ->andReturn($fakeResults);

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(static fn($_request, $response) => $response);

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and(json_decode($response->getContent(), true))->toHaveKey('results');
});

it('passes options through unchanged by default (no filterOptions flag)', function () {
    $request = Request::create(
        '/',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        '{"cc":"NL"}'
    );

    $fakeResults = [
        [
            'carrier' => 'POSTNL',
            'options' => [
                'requiresSignature' => ['available' => true],
                'bogusOption'       => ['available' => true],
            ],
        ],
    ];

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')
        ->once()
        ->with(['cc' => 'NL'])
        ->andReturn($fakeResults);

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(static fn($_request, $response) => $response);

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);
    $payload  = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['results'][0]['options'])->toHaveKeys(['requiresSignature', 'bogusOption']);
});

it('filters options to registered keys when filterOptions=true is in the body', function () {
    $request = Request::create(
        '/',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        '{"cc":"NL","filterOptions":true}'
    );

    $fakeResults = [
        [
            'carrier' => 'POSTNL',
            'options' => [
                'requiresSignature' => ['available' => true],
                'bogusOption'       => ['available' => true],
            ],
        ],
    ];

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')
        ->once()
        ->andReturn($fakeResults);

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(static fn($_request, $response) => $response);

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);
    $payload  = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['results'][0]['options'])->toHaveKey('requiresSignature')
        ->and($payload['results'][0]['options'])->not->toHaveKey('bogusOption');

    foreach (array_keys($payload['results'][0]['options']) as $optionKey) {
        expect(\MyParcelNL\Pdk\Carrier\Model\Carrier::filterRegisteredOptions([$optionKey => []]))
            ->toHaveKey($optionKey);
    }
});

it('passes the payload through to the service unchanged (key conversion happens in the service)', function () {
    $bodyShape = [
        'recipient'          => ['countryCode' => 'NL'],
        'physicalProperties' => ['weight' => ['value' => 2000, 'unit' => 'g']],
        'packageType'        => 'PACKAGE',
        'deliveryType'       => 'STANDARD',
    ];

    $request = Request::create(
        '/',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode($bodyShape)
    );

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')
        ->once()
        ->with($bodyShape)
        ->andReturn([]);

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(static fn($_request, $response) => $response);

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(200);
});

it('reads filterOptions from the query string (kept out of the body so it does not pollute the API payload)', function () {
    $request = Request::create(
        '/?filterOptions=true',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        '{"recipient":{"countryCode":"NL"}}'
    );

    $fakeResults = [
        [
            'carrier' => 'POSTNL',
            'options' => [
                'requiresSignature' => ['available' => true],
                'bogusOption'       => ['available' => true],
            ],
        ],
    ];

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')
        ->once()
        ->withArgs(function (array $payload) {
            return ! array_key_exists('filterOptions', $payload);
        })
        ->andReturn($fakeResults);

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(static fn($_request, $response) => $response);

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);
    $payload  = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['results'][0]['options'])->toHaveKey('requiresSignature')
        ->and($payload['results'][0]['options'])->not->toHaveKey('bogusOption');
});

it('does not forward filterOptions to the SDK args (control flag must be stripped before calling getCapabilities)', function () {
    $request = Request::create(
        '/',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        '{"cc":"NL","filterOptions":true}'
    );

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')
        ->once()
        ->withArgs(function (array $payload) {
            return ! array_key_exists('filterOptions', $payload);
        })
        ->andReturn([]);

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(static fn($_request, $response) => $response);

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(200);
});

it('drops capabilities for carriers this PDK version does not support', function () {
    $request = Request::create(
        '/',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        '{"cc":"NL"}'
    );

    $fakeResults = [
        ['carrier' => 'POSTNL', 'package_types' => ['PACKAGE']],
        ['carrier' => 'UNSUPPORTED_FUTURE_CARRIER', 'package_types' => ['PACKAGE']],
        ['carrier' => 'DHL_FOR_YOU', 'package_types' => ['PACKAGE']],
    ];

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')->once()->andReturn($fakeResults);

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(static fn($_request, $response) => $response);

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);
    $payload  = json_decode($response->getContent(), true);

    expect($response->getStatusCode())->toBe(200)
        ->and(array_column($payload['results'], 'carrier'))
        ->toEqual(['POSTNL', 'DHL_FOR_YOU']);
});

it('passes through sdk core api error response', function () {
    $request = Request::create('/?foo=bar', 'POST', [], [], [], [], '{"invalid":true}');

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\SdkApi\Service\CoreApi\Shipment\CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')
        ->once()
        ->andThrow(new CoreApiException('invalid', 422, [], '{"errors":[{"code":"invalid_request"}]}'));

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(static fn($_request, $response) => $response);

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(422)
        ->and($response->getContent())->toBe('{"errors":[{"code":"invalid_request"}]}');
});

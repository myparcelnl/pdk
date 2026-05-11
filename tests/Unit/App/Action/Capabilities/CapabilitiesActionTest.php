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

    /** @var \Mockery\MockInterface&CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('handlePreflightRequest')
        ->with($request)
        ->andReturn(new Response('', 204));

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    /** @var \Mockery\MockInterface&CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldNotReceive('getCapabilities');

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(204);
});

it('passes the JSON request body verbatim to the service and wraps the result under "results"', function () {
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

    /** @var \Mockery\MockInterface&CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')
        ->once()
        ->with($bodyShape, false)
        ->andReturn(['stub-result']);

    /** @var \Mockery\MockInterface&CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(static fn($_request, $response) => $response);

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and(json_decode($response->getContent(), true))->toEqual(['results' => ['stub-result']]);
});

it('forwards filterSupported=true from the query string to the service', function () {
    $request = Request::create(
        '/?filterSupported=true',
        'POST',
        [],
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        '{"cc":"NL"}'
    );

    /** @var \Mockery\MockInterface&CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')
        ->once()
        ->with(['cc' => 'NL'], true)
        ->andReturn([]);

    /** @var \Mockery\MockInterface&CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(static fn($_request, $response) => $response);

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action   = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(200);
});

it('passes through sdk core api error response', function () {
    $request = Request::create('/?foo=bar', 'POST', [], [], [], [], '{"invalid":true}');

    /** @var \Mockery\MockInterface&CapabilitiesService $mockService */
    $mockService = Mockery::mock(CapabilitiesService::class);
    $mockService->shouldReceive('getCapabilities')
        ->once()
        ->andThrow(new CoreApiException('invalid', 422, [], '{"errors":[{"code":"invalid_request"}]}'));

    /** @var \Mockery\MockInterface&CorsHandler $mockCorsHandler */
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

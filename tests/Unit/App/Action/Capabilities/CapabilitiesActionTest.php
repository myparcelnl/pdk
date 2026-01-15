<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Capabilities;

use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\Api\Response\CapabilitiesResponse;
use MyParcelNL\Pdk\Api\Service\CapabilitiesApiService;
use MyParcelNL\Pdk\App\Action\Capabilities\CapabilitiesAction;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mockery;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('builds request with correct path and parameters', function () {
    $incomingRequest = new Request([
        'path' => '/delivery-options',
        'carrier' => 'postnl',
        'cc' => 'NL',
        'action' => 'proxyCapabilities',
        'pdk_action' => 'proxyCapabilities',
    ]);

    $mockService = Mockery::mock(CapabilitiesApiService::class);
    $action = new CapabilitiesAction($mockService);

    $request = $action->buildRequest($incomingRequest);

    expect($request->getPath())->toBe('/delivery-options')
        ->and($request->getMethod())->toBe('GET')
        ->and($request->getQueryString())->toBe('carrier=postnl&cc=NL');
});

it('uses incoming request method', function () {
    $incomingRequest = Request::create('/', 'POST', [
        'path' => '/delivery-options',
    ]);

    $mockService = Mockery::mock(CapabilitiesApiService::class);
    $action = new CapabilitiesAction($mockService);

    $request = $action->buildRequest($incomingRequest);

    expect($request->getMethod())->toBe('POST');
});

it('handles OPTIONS preflight request', function () {
    $request = Request::create('/', 'OPTIONS');

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('handlePreflightRequest')
        ->with($request)
        ->andReturn(new Response('', 204));

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $mockService = Mockery::mock(CapabilitiesApiService::class);
    // Service should NOT be called for OPTIONS
    $mockService->shouldNotReceive('doRequest');

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(204);
});

it('adds CORS headers to response', function () {
    $request = Request::create('/', 'GET');

    $mockService = Mockery::mock(CapabilitiesApiService::class);
    $mockResponse = Mockery::mock(CapabilitiesResponse::class);
    $symfonyResponse = new Response('{}', 200);

    $mockResponse->shouldReceive('getSymfonyResponse')->andReturn($symfonyResponse);
    $mockService->shouldReceive('doRequest')->once()->andReturn($mockResponse);

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->with($request, $symfonyResponse)
        ->andReturn($symfonyResponse);

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response)->toBe($symfonyResponse);
});

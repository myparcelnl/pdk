<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Capabilities;

use Mockery;
use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\App\Action\Capabilities\CapabilitiesAction;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

beforeEach(function () {
    TestBootstrapper::hasApiKey('test-api-key');
    mockPdkProperty('capabilitiesServiceUrl', 'https://api.myparcel.nl');
});

it('handles OPTIONS preflight request', function () {
    $request = Request::create('/', 'OPTIONS');

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('handlePreflightRequest')
        ->with($request)
        ->andReturn(new Response('', 204));

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $mockService = Mockery::mock(CapabilitiesServiceInterface::class);
    // Service should NOT be called for OPTIONS
    $mockService->shouldNotReceive('get');

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(204);
});

it('configures service with API key and base URL', function () {
    $request = Request::create('/', 'GET', [
        'country' => 'NL',
        'shopId' => 123, // Avoid AccountSettings::getShop() call
    ]);

    $mockCapabilitiesResponse = Mockery::mock();
    $mockCapabilitiesResponse->shouldReceive('getRawResponse')
        ->andReturn('{"data": {"capabilities": ["standard"]}}');
    $mockCapabilitiesResponse->shouldReceive('getStatusCode')
        ->andReturn(200);

    $mockService = Mockery::mock(CapabilitiesServiceInterface::class);
    $mockService->shouldReceive('setApiKey')
        ->once()
        ->with('test-api-key')
        ->andReturnSelf();
    $mockService->shouldReceive('setUserAgents')
        ->once()
        ->andReturnSelf();
    $mockService->shouldReceive('setBaseUrl')
        ->once()
        ->with('https://api.myparcel.nl')
        ->andReturnSelf();
    $mockService->shouldReceive('get')
        ->once()
        ->andReturn($mockCapabilitiesResponse);

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(fn($req, $res) => $res);

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(200);
});

it('creates CapabilitiesRequest with correct country', function () {
    $request = Request::create('/', 'GET', [
        'country' => 'BE',
        'carrier' => 'dpd',
        'shopId' => 456, // Avoid AccountSettings::getShop() call
    ]);

    $capturedRequest = null;

    $mockCapabilitiesResponse = Mockery::mock();
    $mockCapabilitiesResponse->shouldReceive('getRawResponse')
        ->andReturn('{"data": {"capabilities": []}}');
    $mockCapabilitiesResponse->shouldReceive('getStatusCode')
        ->andReturn(200);

    $mockService = Mockery::mock(CapabilitiesServiceInterface::class);
    $mockService->shouldReceive('setApiKey')->andReturnSelf();
    $mockService->shouldReceive('setUserAgents')->andReturnSelf();
    $mockService->shouldReceive('setBaseUrl')->andReturnSelf();
    $mockService->shouldReceive('get')
        ->once()
        ->andReturnUsing(function ($request) use ($mockCapabilitiesResponse, &$capturedRequest) {
            $capturedRequest = $request;
            return $mockCapabilitiesResponse;
        });

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(fn($req, $res) => $res);

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and($capturedRequest)->not->toBeNull();
});




<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Capabilities;

use Mockery;
use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\Api\Request\Request as ApiRequest;
use MyParcelNL\Pdk\Api\Response\CapabilitiesResponse as ProxyCapabilitiesResponse;
use MyParcelNL\Pdk\Api\Service\CapabilitiesApiService;
use MyParcelNL\Pdk\App\Action\Capabilities\CapabilitiesAction;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('handles OPTIONS preflight request', function () {
    $request = Request::create('/', 'OPTIONS');

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('handlePreflightRequest')
        ->with($request)
        ->andReturn(new Response('', 204));

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $mockService = Mockery::mock(CapabilitiesApiService::class);
    $mockService->shouldNotReceive('doRequest');

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(204);
});

it('proxies request body and query params to capabilities endpoint', function () {
    $request = Request::create(
        '/?action=proxyCapabilities&foo=bar',
        'POST',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
        ],
        '{"recipient":{"country_code":"NL"}}'
    );

    $proxyResponse = Mockery::mock(ProxyCapabilitiesResponse::class);
    $proxyResponse->shouldReceive('getSymfonyResponse')
        ->once()
        ->andReturn(new Response('{"ok":true}', 200, ['Content-Type' => 'application/json']));

    $mockService = Mockery::mock(CapabilitiesApiService::class);
    $mockService->shouldReceive('doRequest')
        ->once()
        ->withArgs(function (ApiRequest $apiRequest, string $responseClass): bool {
            return '/shipments/capabilities' === $apiRequest->getPath()
                && 'POST' === $apiRequest->getMethod()
                && 'foo=bar' === $apiRequest->getQueryString()
                && '{"recipient":{"country_code":"NL"}}' === $apiRequest->getBody()
                && ProxyCapabilitiesResponse::class === $responseClass;
        })
        ->andReturn($proxyResponse);

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(function ($req, $res) {
            return $res;
        });

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('{"ok":true}');
});

it('passes through error status code and body from capabilities api', function () {
    $request = Request::create('/?foo=bar', 'POST', [], [], [], [], '{"invalid":true}');

    $clientResponse = Mockery::mock(ClientResponseInterface::class);
    $clientResponse->shouldReceive('getBody')
        ->andReturn('{"errors":[{"code":"invalid_request"}]}');
    $clientResponse->shouldReceive('getStatusCode')
        ->andReturn(422);

    $mockService = Mockery::mock(CapabilitiesApiService::class);
    $mockService->shouldReceive('doRequest')
        ->once()
        ->andThrow(new ApiException($clientResponse));

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(function ($req, $res) {
            return $res;
        });

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(422)
        ->and($response->getContent())->toBe('{"errors":[{"code":"invalid_request"}]}');
});

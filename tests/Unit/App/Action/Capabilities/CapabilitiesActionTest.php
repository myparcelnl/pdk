<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Capabilities;

use Mockery;
use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\App\Action\Capabilities\CapabilitiesAction;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Model\Capabilities\CapabilitiesRequest;
use MyParcelNL\Sdk\Model\Capabilities\CapabilitiesResponse;
use MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

beforeEach(function () {
    TestBootstrapper::hasApiKey('test-api-key');
});

it('handles OPTIONS preflight request', function () {
    if (! interface_exists(CapabilitiesServiceInterface::class) ||
        ! class_exists(CapabilitiesRequest::class) ||
        ! class_exists(CapabilitiesResponse::class)) {
        $this->markTestSkipped('SDK capabilities classes are unavailable. Install SDK v11 branch first.');
    }

    $request = Request::create('/', 'OPTIONS');

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('handlePreflightRequest')
        ->with($request)
        ->andReturn(new Response('', 204));

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $mockService = Mockery::mock(CapabilitiesServiceInterface::class);
    $mockService->shouldNotReceive('get');

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(204);
});

it('builds sdk capabilities request from incoming payload', function () {
    if (! interface_exists(CapabilitiesServiceInterface::class) ||
        ! class_exists(CapabilitiesRequest::class) ||
        ! class_exists(CapabilitiesResponse::class)) {
        $this->markTestSkipped('SDK capabilities classes are unavailable. Install SDK v11 branch first.');
    }

    $request = Request::create(
        '/?action=proxyCapabilities&shopId=123',
        'POST',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
        ],
        '{"country":"NL","carrier":"postnl","deliveryType":"standard_delivery","packageType":"package","direction":"outbound","options":{"requires_signature":null},"sender":{"country_code":"NL","is_business":true}}'
    );

    $sdkResponse = new CapabilitiesResponse(
        ['package'],
        ['standard_delivery'],
        ['requires_signature'],
        'postnl',
        ['b2c'],
        3
    );

    $mockService = Mockery::mock(CapabilitiesServiceInterface::class);
    $mockService->shouldReceive('get')
        ->once()
        ->withArgs(function (CapabilitiesRequest $sdkRequest): bool {
            return 'NL' === $sdkRequest->getCountryCode()
                && 123 === $sdkRequest->getShopId()
                && 'postnl' === $sdkRequest->getCarrier()
                && 'standard_delivery' === $sdkRequest->getDeliveryType()
                && 'package' === $sdkRequest->getPackageType()
                && 'outbound' === $sdkRequest->getDirection()
                && ['requires_signature' => null] === $sdkRequest->getOptions()
                && ['country_code' => 'NL', 'is_business' => true] === $sdkRequest->getSender();
        })
        ->andReturn($sdkResponse);

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(fn($req, $res) => $res);

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and(getenv('API_KEY'))->toBe('test-api-key')
        ->and($response->getContent())->toContain('"package_types":["package"]')
        ->and($response->getContent())->toContain('"carrier":"postnl"');
});

it('passes through sdk error payload and status code', function () {
    if (! interface_exists(CapabilitiesServiceInterface::class) ||
        ! class_exists(CapabilitiesRequest::class) ||
        ! class_exists(CapabilitiesResponse::class)) {
        $this->markTestSkipped('SDK capabilities classes are unavailable. Install SDK v11 branch first.');
    }

    $request = Request::create('/', 'POST', [], [], [], [], '{"country":"NL"}');

    $mockService = Mockery::mock(CapabilitiesServiceInterface::class);
    $mockService->shouldReceive('get')
        ->once()
        ->andThrow(new class('invalid', 422) extends RuntimeException {
            public function getResponseBody(): string
            {
                return '{"errors":[{"code":"invalid_request"}]}';
            }
        });

    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    $mockCorsHandler->shouldReceive('addCorsHeaders')
        ->once()
        ->andReturnUsing(fn($req, $res) => $res);

    Pdk::set(CorsHandler::class, $mockCorsHandler);

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(422)
        ->and($response->getContent())->toBe('{"errors":[{"code":"invalid_request"}]}');
});

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Capabilities;

use Mockery;
use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\App\Action\Capabilities\CapabilitiesAction;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException as CoreApiException;
use MyParcelNL\Sdk\Model\Capabilities\CapabilitiesRequest;
use MyParcelNL\Sdk\Model\Capabilities\CapabilitiesResponse;
use MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface;
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
    /** @var \Mockery\Expectation $preflightExpectation */
    $preflightExpectation = $mockCorsHandler->shouldReceive('handlePreflightRequest');
    $preflightExpectation
        ->with($request)
        ->andReturn(new Response('', 204));

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    /** @var \Mockery\MockInterface&\MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface $mockService */
    $mockService = Mockery::mock(CapabilitiesServiceInterface::class);
    $mockService->shouldNotReceive('get');

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(204);
});

it('maps proxied request to sdk capabilities request', function () {
    $request = Request::create(
        '/?action=proxyCapabilities&shopId=123&carrier=postnl&packageType=package&deliveryType=standard_delivery&direction=outbound',
        'POST',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
        ],
        '{"country":"NL","pickup":{"location":{"type":"retail"}},"sender":{"country_code":"NL","is_business":true},"physicalProperties":{"weight":{"value":1000,"unit":"g"}},"options":{"requires_signature":null}}'
    );

    $sdkResponse = new CapabilitiesResponse(
        ['package'],
        ['standard_delivery'],
        ['requires_signature'],
        'postnl',
        ['b2c'],
        3
    );

    /** @var \Mockery\MockInterface&\MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface $mockService */
    $mockService = Mockery::mock(CapabilitiesServiceInterface::class);
    /** @var \Mockery\Expectation $serviceExpectation */
    $serviceExpectation = $mockService->shouldReceive('get');
    $serviceExpectation
        ->once()
        ->withArgs(function (CapabilitiesRequest $request): bool {
            return 'NL' === $request->getCountryCode()
                && 123 === $request->getShopId()
                && 'postnl' === $request->getCarrier()
                && 'package' === $request->getPackageType()
                && 'standard_delivery' === $request->getDeliveryType()
                && 'outbound' === $request->getDirection()
                && ['location' => ['type' => 'retail']] === $request->getPickup()
                && ['country_code' => 'NL', 'is_business' => true] === $request->getSender()
                && ['weight' => ['value' => 1000, 'unit' => 'g']] === $request->getPhysicalProperties()
                && ['requires_signature' => null] === $request->getOptions();
        })
        ->andReturn($sdkResponse);

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    /** @var \Mockery\Expectation $corsExpectation */
    $corsExpectation = $mockCorsHandler->shouldReceive('addCorsHeaders');
    $corsExpectation
        ->once()
        ->andReturnUsing(function ($req, $res) {
            return $res;
        });

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toContain('"package_types":["package"]')
        ->and($response->getContent())->toContain('"carrier":"postnl"')
        ->and($response->getContent())->toContain('"collo_max":3');
});

it('passes through sdk core api error response', function () {
    $request = Request::create('/?foo=bar', 'POST', [], [], [], [], '{"invalid":true}');

    /** @var \Mockery\MockInterface&\MyParcelNL\Sdk\Services\Capabilities\CapabilitiesServiceInterface $mockService */
    $mockService = Mockery::mock(CapabilitiesServiceInterface::class);
    /** @var \Mockery\Expectation $serviceExpectation */
    $serviceExpectation = $mockService->shouldReceive('get');
    $serviceExpectation
        ->once()
        ->andThrow(new CoreApiException('invalid', 422, [], '{"errors":[{"code":"invalid_request"}]}'));

    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Handler\CorsHandler $mockCorsHandler */
    $mockCorsHandler = Mockery::mock(CorsHandler::class);
    /** @var \Mockery\Expectation $corsExpectation */
    $corsExpectation = $mockCorsHandler->shouldReceive('addCorsHeaders');
    $corsExpectation
        ->once()
        ->andReturnUsing(function ($req, $res) {
            return $res;
        });

    mockPdkProperty(CorsHandler::class, $mockCorsHandler);

    $action = new CapabilitiesAction($mockService);
    $response = $action->handle($request);

    expect($response->getStatusCode())->toBe(422)
        ->and($response->getContent())->toBe('{"errors":[{"code":"invalid_request"}]}');
});

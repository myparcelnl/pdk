<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint;

use MyParcelNL\Pdk\App\Endpoint\GetDeliveryOptionsEndpoint;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns delivery options for valid order id', function () {
    $mockDeliveryOptions = mock(DeliveryOptions::class);

    $mockOrder = mock(PdkOrder::class)
        ->shouldReceive('getDeliveryOptions')
        ->andReturn($mockDeliveryOptions)
        ->getMock();

    $mockRepository = mock(PdkOrderRepositoryInterface::class)
        ->shouldReceive('get')
        ->with('123')
        ->andReturn($mockOrder)
        ->getMock();

    Pdk::shouldReceive('get')
        ->with(PdkOrderRepositoryInterface::class)
        ->andReturn($mockRepository);

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(200);

    $content = json_decode($response->getContent(), true);
    expect($content['orderId'])->toBe('123');
    expect($content['deliveryOptions'])->toBeArray();
});

it('returns 400 error when order id is missing', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request();

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(400);

    $content = json_decode($response->getContent(), true);
    expect($content['type'])->toBe('https://errors.myparcel/validation-error');
    expect($content['detail'])->toContain('orderId');
});

it('validates request correctly', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();

    // Valid request with orderId
    $validRequest = new Request(['orderId' => '123']);
    expect($endpoint->validate($validRequest))->toBeTrue();

    // Invalid request without orderId
    $invalidRequest = new Request();
    expect($endpoint->validate($invalidRequest))->toBeFalse();
});

it('extracts order id from query parameter', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '456']);

    expect($endpoint->validate($request))->toBeTrue();
});

it('extracts order id from route attributes', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request();
    $request->attributes->set('orderId', '789');

    expect($endpoint->validate($request))->toBeTrue();
});

it('extracts order id from json body for post request', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(
        [],
        [],
        [],
        [],
        [],
        ['REQUEST_METHOD' => 'POST', 'CONTENT_TYPE' => 'application/json'],
        json_encode(['orderId' => '101'])
    );

    expect($endpoint->validate($request))->toBeTrue();
});

it('returns 500 error when repository throws exception', function () {
    $mockRepository = mock(PdkOrderRepositoryInterface::class)
        ->shouldReceive('get')
        ->with('123')
        ->andThrow(new \RuntimeException('Repository error'))
        ->getMock();

    Pdk::shouldReceive('get')
        ->with(PdkOrderRepositoryInterface::class)
        ->andReturn($mockRepository);

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(500);

    $content = json_decode($response->getContent(), true);
    expect($content['type'])->toBe('https://errors.myparcel/delivery-options-error');
});

it('returns correct endpoint identifier', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $reflection = new \ReflectionClass($endpoint);
    $method = $reflection->getMethod('getEndpointIdentifier');
    $method->setAccessible(true);

    expect($method->invoke($endpoint))->toBe('delivery-options');
});

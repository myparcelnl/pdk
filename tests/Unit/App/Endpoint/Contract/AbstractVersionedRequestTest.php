<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint\Contract;

use MyParcelNL\Pdk\App\Endpoint\Request\GetDeliveryOptionsV1Request;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('gets validation errors if set', function () {
    $httpRequest = new Request();
    $request     = new GetDeliveryOptionsV1Request($httpRequest);

    $request->validate();

    expect($request->getValidationErrors())->toBeArray();
    expect($request->getValidationErrors())->toHaveKey('orderId');
});

it('gets validation errors as a string', function () {
    $httpRequest = new Request();
    $request     = new GetDeliveryOptionsV1Request($httpRequest);

    $request->validate();
    $response = $request->createValidationErrorResponse();
    $content  = json_decode($response->getContent(), true);

    expect($content['detail'])->toContain('orderId');
});

it('creates a validation response', function () {
    $httpRequest = new Request();
    $request     = new GetDeliveryOptionsV1Request($httpRequest);

    $request->validate();
    $response = $request->createValidationErrorResponse();

    expect($response->getStatusCode())->toBe(400);

    $content = json_decode($response->getContent(), true);
    expect($content['status'])->toBe(400);
    expect($content['title'])->toBe('Invalid Request');
});

it('creates a not found response', function () {
    $httpRequest = new Request(['orderId' => '123']);
    $request     = new GetDeliveryOptionsV1Request($httpRequest);

    $response = $request->createNotFoundErrorResponse('Order not found');

    expect($response->getStatusCode())->toBe(404);

    $content = json_decode($response->getContent(), true);
    expect($content['status'])->toBe(404);
    expect($content['title'])->toBe('Not Found');
    expect($content['detail'])->toBe('Order not found');
});

it('creates an internal server error response', function () {
    $httpRequest = new Request(['orderId' => '123']);
    $request     = new GetDeliveryOptionsV1Request($httpRequest);

    $response = $request->createInternalServerErrorResponse('Something went wrong');

    expect($response->getStatusCode())->toBe(500);

    $content = json_decode($response->getContent(), true);
    expect($content['status'])->toBe(500);
    expect($content['title'])->toBe('Internal Server Error');
    expect($content['detail'])->toBe('Something went wrong');
});

it('correctly decodes a json body', function () {
    $httpRequest = new Request([], [], [], [], [], [], json_encode(['foo' => 'bar']));
    $request     = new GetDeliveryOptionsV1Request($httpRequest);

    // Use reflection to access protected method
    $reflection = new \ReflectionClass($request);
    $method     = $reflection->getMethod('getRequestBody');
    $method->setAccessible(true);

    $body = $method->invoke($request);

    expect($body)->toBe(['foo' => 'bar']);
});

it('getValidationErrorMessage() returns null if there are no validation errors', function () {
    $httpRequest = new Request(['orderId' => '123']);
    $request     = new GetDeliveryOptionsV1Request($httpRequest);

    $request->validate();

    // Use reflection to access protected method
    $reflection = new \ReflectionClass($request);
    $method     = $reflection->getMethod('getValidationErrorMessage');
    $method->setAccessible(true);

    expect($method->invoke($request))->toBeNull();
});

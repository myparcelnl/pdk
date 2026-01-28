<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Endpoint\Contract\AbstractEndpoint;
use MyParcelNL\Pdk\App\Endpoint\EndpointService;
use MyParcelNL\Pdk\App\Endpoint\PdkEndpoint;
use MyParcelNL\Pdk\Facade\Config;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('processes endpoint request successfully', function () {
    $mockEndpoint = mock(AbstractEndpoint::class)
        ->shouldReceive('validate')
        ->with(anInstanceOf(Request::class))
        ->andReturn(true)
        ->shouldReceive('handle')
        ->with(anInstanceOf(Request::class))
        ->andReturn(new Response('{"success": true}', 200))
        ->getMock();

    Config::shouldReceive('get')
        ->with('endpoints', [])
        ->andReturn([PdkEndpoint::DELIVERY_OPTIONS => 'TestEndpoint']);

    Pdk::shouldReceive('get')
        ->with('TestEndpoint')
        ->andReturn($mockEndpoint);

    $service = new EndpointService();
    $request = new Request();

    $response = $service->handleRequest($request, PdkEndpoint::deliveryOptions());

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('{"success": true}');
});

it('returns validation error when endpoint validation fails', function () {
    $mockEndpoint = mock(AbstractEndpoint::class)
        ->shouldReceive('validate')
        ->with(anInstanceOf(Request::class))
        ->andReturn(false)
        ->getMock();

    Config::shouldReceive('get')
        ->with('endpoints', [])
        ->andReturn([PdkEndpoint::DELIVERY_OPTIONS => 'TestEndpoint']);

    Pdk::shouldReceive('get')
        ->with('TestEndpoint')
        ->andReturn($mockEndpoint);

    $service = new EndpointService();
    $request = new Request();

    $response = $service->handleRequest($request, PdkEndpoint::deliveryOptions());

    expect($response->getStatusCode())->toBe(400);

    $content = json_decode($response->getContent(), true);
    expect($content['type'])->toBe('https://errors.myparcel/validation-error');
    expect($content['title'])->toBe('Validation Error');
});

it('returns not found error for unknown endpoint', function () {
    Config::shouldReceive('get')
        ->with('endpoints', [])
        ->andReturn([]);

    $service = new EndpointService();
    $request = new Request();

    $response = $service->handleRequest($request, PdkEndpoint::deliveryOptions());

    expect($response->getStatusCode())->toBe(404);

    $content = json_decode($response->getContent(), true);
    expect($content['type'])->toBe('https://errors.myparcel/not-found');
    expect($content['detail'])->toContain('delivery-options');
});

it('handles exceptions gracefully', function () {
    $mockEndpoint = mock(AbstractEndpoint::class)
        ->shouldReceive('validate')
        ->with(anInstanceOf(Request::class))
        ->andReturn(true)
        ->shouldReceive('handle')
        ->with(anInstanceOf(Request::class))
        ->andThrow(new \Exception('Test exception'))
        ->getMock();

    Config::shouldReceive('get')
        ->with('endpoints', [])
        ->andReturn([PdkEndpoint::DELIVERY_OPTIONS => 'TestEndpoint']);

    Pdk::shouldReceive('get')
        ->with('TestEndpoint')
        ->andReturn($mockEndpoint);

    $service = new EndpointService();
    $request = new Request();

    $response = $service->handleRequest($request, PdkEndpoint::deliveryOptions());

    expect($response->getStatusCode())->toBe(500);

    $content = json_decode($response->getContent(), true);
    expect($content['type'])->toBe('https://errors.myparcel/internal-error');
});

it('validates invalid endpoint strings', function () {
    expect(function () {
        PdkEndpoint::fromClass('InvalidEndpoint');
    })->toThrow(InvalidArgumentException::class);
});

it('creates valid endpoints from class', function () {
    $endpoint = PdkEndpoint::fromClass(GetDeliveryOptionsEndpoint::class);
    expect($endpoint->getHandlerClass())->toBe(GetDeliveryOptionsEndpoint::class);
    expect((string) $endpoint)->toBe(GetDeliveryOptionsEndpoint::class);
});

it('provides valid endpoint factory methods', function () {
    $endpoint = PdkEndpoint::deliveryOptions();
    expect($endpoint->getValue())->toBe('delivery-options');
    expect($endpoint->equals(PdkEndpoint::fromString('delivery-options')))->toBe(true);
});

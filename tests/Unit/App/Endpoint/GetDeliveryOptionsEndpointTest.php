<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint;

use MyParcelNL\Pdk\App\Endpoint\Handler\GetDeliveryOptionsEndpoint;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Bootstrap\MockExceptionPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockNotFoundPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use function DI\autowire;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns delivery options for valid order id', function () {
    // Create and store a mock order using the factory pattern
    factory(PdkOrder::class)
        ->withExternalIdentifier('123')
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('postnl')
                ->withDate('2024-01-15')
                ->withPackageType('package')
        )
        ->store();

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(200);

    $content = json_decode($response->getContent(), true);
    expect($content)->toBeArray();
    expect($content)->toHaveKey('carrier', 'POSTNL');
    expect($content)->toHaveKey('date');
    expect($content)->toHaveKey('packageType', 'PACKAGE');
});

it('returns 400 error when order id is missing', function () {
    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request();

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(400);

    $content = json_decode($response->getContent(), true);
    expect($content)->toHaveKey('type');
    expect($content)->toHaveKey('title');
    expect($content)->toHaveKey('status');
    expect($content['status'])->toBe(400);
    expect($content)->toHaveKey('detail');
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

it('returns 404 error when order not found', function () {
    // Recreate PDK instance with a repository that always returns null for find()
    MockPdkFactory::create([
        PdkOrderRepositoryInterface::class => autowire(MockNotFoundPdkOrderRepository::class),
    ]);

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '999']);

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(404);

    $content = json_decode($response->getContent(), true);
    expect($content['status'])->toBe(404);
    expect($content['detail'])->toContain('Order not found');

    // Reset PDK instance so subsequent tests get the default mock
    Pdk::setPdkInstance(null);
    MockPdkFactory::create();
});


it('logs an error and returns a 500 error when an exception occurs', function () {
    // Recreate PDK instance with a repository that throws an exception
    MockPdkFactory::create([
        PdkOrderRepositoryInterface::class => autowire(MockExceptionPdkOrderRepository::class),
    ]);

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(500);

    $content = json_decode($response->getContent(), true);
    expect($content['status'])->toBe(500);
    expect($content['detail'])->toContain('Something went wrong.');

    // Reset PDK instance so subsequent tests get the default mock
    Pdk::setPdkInstance(null);
    MockPdkFactory::create();
});

it('detects API version from request headers', function () {
    // Create and store a mock order
    factory(PdkOrder::class)
        ->withExternalIdentifier('123')
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('postnl')
        )
        ->store();

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);
    $request->headers->set('Content-Type', 'application/json; version=1');

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Content-Type'))->toContain('version=1');
});

it('falls back to v1 by default when no API version is specified', function () {
    // Create and store a mock order
    factory(PdkOrder::class)
        ->withExternalIdentifier('123')
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier('postnl')
        )
        ->store();

    $endpoint = new GetDeliveryOptionsEndpoint();
    $request = new Request(['orderId' => '123']);

    $response = $endpoint->handle($request);

    expect($response->getStatusCode())->toBe(200);
    expect($response->headers->get('Content-Type'))->toContain('version=1');
});

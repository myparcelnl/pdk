<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Middleware;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

/**
 * Build a Guzzle Client that uses a MockHandler and FeatureFlagMiddleware,
 * returning the mock so tests can enqueue responses and inspect the request.
 */
function makeFeatureFlagClient(): array
{
    $mock  = new MockHandler();
    $stack = HandlerStack::create($mock);
    $stack->push(FeatureFlagMiddleware::forApiRequests());

    return [new Client(['handler' => $stack]), $mock];
}

it('forApiRequests returns a callable', function () {
    expect(FeatureFlagMiddleware::forApiRequests())->toBeCallable();
});

it('adds the configured flag headers to the request', function () {
    mockPdkProperty('apiFeatureFlags', ['x-dmp-no-tracking']);

    [$client, $mock] = makeFeatureFlagClient();
    $mock->append(new Response(200, [], '{}'));

    $client->get('https://api.myparcel.nl/test');

    expect($mock->getLastRequest()->getHeaderLine('x-dmp-no-tracking'))->toBe('true');
});

it('adds every configured flag header', function () {
    mockPdkProperty('apiFeatureFlags', ['x-dmp-no-tracking', 'x-dmp-other-behaviour']);

    [$client, $mock] = makeFeatureFlagClient();
    $mock->append(new Response(200, [], '{}'));

    $client->get('https://api.myparcel.nl/test');

    expect($mock->getLastRequest()->getHeaderLine('x-dmp-no-tracking'))->toBe('true')
        ->and($mock->getLastRequest()->getHeaderLine('x-dmp-other-behaviour'))->toBe('true');
});

it('leaves the request untouched when no flags are configured', function () {
    mockPdkProperty('apiFeatureFlags', []);

    [$client, $mock] = makeFeatureFlagClient();
    $mock->append(new Response(200, [], '{}'));

    $client->get('https://api.myparcel.nl/test');

    expect($mock->getLastRequest()->hasHeader('x-dmp-no-tracking'))->toBeFalse();
});

it('keeps headers the request already had', function () {
    mockPdkProperty('apiFeatureFlags', ['x-dmp-no-tracking']);

    [$client, $mock] = makeFeatureFlagClient();
    $mock->append(new Response(200, [], '{}'));

    $client->get('https://api.myparcel.nl/test', ['headers' => ['Authorization' => 'bearer abc']]);

    expect($mock->getLastRequest()->getHeaderLine('Authorization'))->toBe('bearer abc')
        ->and($mock->getLastRequest()->getHeaderLine('x-dmp-no-tracking'))->toBe('true');
});

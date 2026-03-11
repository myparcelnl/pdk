<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Middleware;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Psr\Log\LogLevel;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

/**
 * Build a Guzzle Client that uses a MockHandler and LoggingMiddleware,
 * returning the mock so tests can enqueue responses.
 */
function makeLoggingClient(): array
{
    $mock  = new MockHandler();
    $stack = HandlerStack::create($mock);
    $stack->push(LoggingMiddleware::forApiRequests());

    return [new Client(['handler' => $stack]), $mock];
}

// forApiRequests() returns a callable
it('forApiRequests returns a callable', function () {
    expect(LoggingMiddleware::forApiRequests())->toBeCallable();
});

// Request logging
it('logs debug message with method and uri before sending request', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    [$client, $mock] = makeLoggingClient();
    $mock->append(new Response(200, [], '{}'));

    $client->get('http://example.com/api/test');

    $debugLogs = $logger->getLogs(LogLevel::DEBUG);
    $requestLog = array_values(array_filter($debugLogs, fn($l) => $l['message'] === '[PDK]: Sending API request'));

    expect($requestLog)->toHaveCount(1)
        ->and($requestLog[0]['context']['method'])->toBe('GET')
        ->and($requestLog[0]['context']['uri'])->toBe('http://example.com/api/test');
});

// Successful response logging
it('logs debug message with status and decoded body on successful response', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    [$client, $mock] = makeLoggingClient();
    $mock->append(new Response(200, [], json_encode(['foo' => 'bar'])));

    $client->get('http://example.com/api/test');

    $debugLogs   = $logger->getLogs(LogLevel::DEBUG);
    $responseLog = array_values(array_filter($debugLogs, fn($l) => $l['message'] === '[PDK]: Received API response'));

    expect($responseLog)->toHaveCount(1)
        ->and($responseLog[0]['context']['status'])->toBe(200)
        ->and($responseLog[0]['context']['body'])->toBe(['foo' => 'bar']);
});

it('logs null body when response body is empty', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    [$client, $mock] = makeLoggingClient();
    $mock->append(new Response(204, [], ''));

    $client->get('http://example.com/api/test');

    $debugLogs   = $logger->getLogs(LogLevel::DEBUG);
    $responseLog = array_values(array_filter($debugLogs, fn($l) => $l['message'] === '[PDK]: Received API response'));

    expect($responseLog)->toHaveCount(1)
        ->and($responseLog[0]['context']['body'])->toBeNull();
});

it('rewinds the response body after logging so the SDK can still read it', function () {
    [$client, $mock] = makeLoggingClient();
    $mock->append(new Response(200, [], json_encode(['result' => 42])));

    // The SDK reads the body via the response object; if the body was not rewound
    // after logging, getBody()->getContents() would return an empty string.
    $response = $client->get('http://example.com/api/test');

    expect((string) $response->getBody())->toBe(json_encode(['result' => 42]));
});

// Error logging
it('logs error message with error details on request exception', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    [$client, $mock] = makeLoggingClient();
    $errorResponse   = new Response(500, ['X-Req' => 'id-123'], json_encode(['message' => 'Server Error']));
    $mock->append(new RequestException('Server Error', new Request('POST', 'http://example.com/fail'), $errorResponse));

    try {
        $client->post('http://example.com/fail');
    } catch (\Throwable $e) {
        // Expected
    }

    $errorLogs = $logger->getLogs(LogLevel::ERROR);

    expect($errorLogs)->toHaveCount(1)
        ->and($errorLogs[0]['message'])->toBe('[PDK]: API request failed')
        ->and($errorLogs[0]['context']['error'])->toContain('Server Error')
        ->and($errorLogs[0]['context']['responseBody'])->toBe(['message' => 'Server Error'])
        ->and($errorLogs[0]['context']['responseHeaders'])->toHaveKey('X-Req');
});

it('logs error without responseBody and responseHeaders when exception has no response', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    [$client, $mock] = makeLoggingClient();
    $mock->append(new RequestException('Connection refused', new Request('GET', 'http://example.com/no-response')));

    try {
        $client->get('http://example.com/no-response');
    } catch (\Throwable $e) {
        // Expected
    }

    $errorLogs = $logger->getLogs(LogLevel::ERROR);

    expect($errorLogs)->toHaveCount(1)
        ->and($errorLogs[0]['context'])->not->toHaveKey('responseBody')
        ->and($errorLogs[0]['context'])->not->toHaveKey('responseHeaders');
});

it('re-throws the exception after logging', function () {
    [$client, $mock] = makeLoggingClient();
    $mock->append(new RequestException('Fail', new Request('GET', 'http://example.com/rethrow')));

    expect(fn() => $client->get('http://example.com/rethrow'))
        ->toThrow(RequestException::class);
});

<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\ApiException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LogLevel;

use function DI\value;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

// Create a concrete test implementation for testing abstract methods
class ConcreteSdkApiServiceForTest extends AbstractSdkApiService
{
    public function getApiConfig()
    {
        return null; // Dummy implementation
    }

    // Expose protected methods for testing
    public function publicGetApiKey(): ?string
    {
        return $this->getApiKey();
    }

    public function publicGetUserAgent(): string
    {
        return $this->getUserAgent();
    }

    public function publicIsAcceptanceEnvironment(): bool
    {
        return $this->isAcceptanceEnvironment();
    }

    public function publicExecuteOperationWithErrorHandling(callable $operation, string $name)
    {
        return $this->executeOperationWithErrorHandling($operation, $name);
    }
}

// Tests for getApiKey()
it('returns null when API key is not set', function () {
    $service = new ConcreteSdkApiServiceForTest();

    expect($service->publicGetApiKey())->toBeNull();
});

it('returns API key when set in settings', function () {
    TestBootstrapper::hasApiKey('test-api-key-12345');

    $service = new ConcreteSdkApiServiceForTest();

    expect($service->publicGetApiKey())->toBe('test-api-key-12345');
});

// Tests for getUserAgent()
it('builds user agent with PDK defaults', function () {
    $service = new ConcreteSdkApiServiceForTest();
    $userAgent = $service->publicGetUserAgent();
    $pdkVersion = Pdk::get('pdkVersion');

    expect($userAgent)
        ->toBeString()
        ->toContain('MyParcelNL-PDK/' . $pdkVersion)
        ->toContain('php/' . PHP_VERSION);
});

it('includes custom user agents from config', function () {
    $pdk = MockPdkFactory::create([
        'userAgent' => value([
            'MyPlatform' => '2.0.0',
            'CustomApp'  => '1.5.3',
        ]),
    ]);

    $service = new ConcreteSdkApiServiceForTest();
    $userAgent = $service->publicGetUserAgent();

    expect($userAgent)
        ->toContain('MyPlatform/2.0.0')
        ->toContain('CustomApp/1.5.3')
        ->toContain('MyParcelNL-PDK/')
        ->toContain('php/');
});

it('user agent format follows Platform/Version pattern', function () {
    $service = new ConcreteSdkApiServiceForTest();
    $userAgent = $service->publicGetUserAgent();

    // Should match: "Platform1/1.0.0 Platform2/2.0.0"
    expect($userAgent)->toMatch('/^[\w-]+\/[\d.]+(?: [\w-]+\/[\d.]+)*$/');
});

// Tests for isAcceptanceEnvironment()
it('returns false when environment is production', function () {
    TestBootstrapper::hasAccount();

    factory(AccountSettings::class)
        ->withEnvironment(Config::ENVIRONMENT_PRODUCTION)
        ->store();

    $service = new ConcreteSdkApiServiceForTest();

    expect($service->publicIsAcceptanceEnvironment())->toBeFalse();
});

it('returns true when environment is acceptance', function () {
    TestBootstrapper::hasAccount();

    factory(AccountSettings::class)
        ->withEnvironment(Config::ENVIRONMENT_ACCEPTANCE)
        ->store();

    $service = new ConcreteSdkApiServiceForTest();

    expect($service->publicIsAcceptanceEnvironment())->toBeTrue();
});

it('logs debug message when using acceptance environment', function () {
    TestBootstrapper::hasAccount();

    factory(AccountSettings::class)
        ->withEnvironment(Config::ENVIRONMENT_ACCEPTANCE)
        ->store();

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    $service = new ConcreteSdkApiServiceForTest();
    $service->publicIsAcceptanceEnvironment();

    $logs = $logger->getLogs(LogLevel::DEBUG);

    expect($logs)
        ->toHaveCount(1)
        ->and($logs[0]['message'])
        ->toContain('Using acceptance API URL');
});

it('returns false when environment is not set', function () {
    factory(AccountSettings::class)
        ->store();

    $service = new ConcreteSdkApiServiceForTest();
    $result  = $service->publicIsAcceptanceEnvironment();

    expect($result)->toBeFalse();
});

// Tests for executeOperationWithErrorHandling()
it('processes a 200 response and logs debug message', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    $service = new ConcreteSdkApiServiceForTest();

    $mockBody = \Mockery::mock(StreamInterface::class);
    $mockBody->shouldReceive('__toString')->andReturn('{"success": true}');

    $mockResponse = \Mockery::mock(ResponseInterface::class);
    $mockResponse->shouldReceive('getStatusCode')->andReturn(200);
    $mockResponse->shouldReceive('getBody')->andReturn($mockBody);

    $operation = fn() => $mockResponse;

    $result = $service->publicExecuteOperationWithErrorHandling($operation, 'testOperation');

    expect($result)->toBe($mockResponse);

    $logs = $logger->getLogs(LogLevel::DEBUG);

    expect($logs)
        ->toHaveCount(1)
        ->and($logs[0]['message'])
        ->toBe('[PDK]: Successfully sent request')
        ->and($logs[0]['context']['operation'])
        ->toBe('testOperation')
        ->and($logs[0]['context']['response']['code'])
        ->toBe(200)
        ->and($logs[0]['context']['response']['body'])
        ->toBe(['success' => true]);
});

it('handles empty response body gracefully', function () {
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    $service = new ConcreteSdkApiServiceForTest();

    $mockBody = \Mockery::mock(StreamInterface::class);
    $mockBody->shouldReceive('__toString')->andReturn('');

    $mockResponse = \Mockery::mock(ResponseInterface::class);
    $mockResponse->shouldReceive('getStatusCode')->andReturn(204);
    $mockResponse->shouldReceive('getBody')->andReturn($mockBody);

    $operation = fn() => $mockResponse;

    $result = $service->publicExecuteOperationWithErrorHandling($operation, 'testOperation');

    expect($result)->toBe($mockResponse);

    $logs = $logger->getLogs(LogLevel::DEBUG);

    expect($logs[0]['context']['response']['body'])->toBeNull();
});

it('catches and logs an OpenApi client ApiException with full context', function () {
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    $service = new ConcreteSdkApiServiceForTest();

    $exception = new ApiException(
        'API Error occurred',
        400,
        ['X-Request-Id' => 'abc123'],
        '{"error": "Bad request"}'
    );

    $operation = function () use ($exception) {
        throw $exception;
    };

    expect(fn() => $service->publicExecuteOperationWithErrorHandling($operation, 'failOperation'))
        ->toThrow(ApiException::class);

    $errorLogs = $logger->getLogs(LogLevel::ERROR);

    expect($errorLogs)
        ->toHaveCount(1)
        ->and($errorLogs[0]['message'])
        ->toBe('[PDK]: An exception was thrown while sending request')
        ->and($errorLogs[0]['context']['operation'])
        ->toBe('failOperation')
        ->and($errorLogs[0]['context']['error'])
        ->toBe('API Error occurred')
        ->and($errorLogs[0]['context']['code'])
        ->toBe(400)
        ->and($errorLogs[0]['context']['responseBody'])
        ->toBe('{"error": "Bad request"}')
        ->and($errorLogs[0]['context']['responseHeaders'])
        ->toBe(['X-Request-Id' => 'abc123']);
});

it('catches and logs generic Throwable without ApiException context', function () {
    $logger = Pdk::get(PdkLoggerInterface::class);
    $logger->clear();

    $service = new ConcreteSdkApiServiceForTest();

    $exception = new \RuntimeException('Generic runtime error', 500);

    $operation = function () use ($exception) {
        throw $exception;
    };

    expect(fn() => $service->publicExecuteOperationWithErrorHandling($operation, 'genericError'))
        ->toThrow(\RuntimeException::class);

    $errorLogs = $logger->getLogs(LogLevel::ERROR);

    expect($errorLogs)
        ->toHaveCount(1)
        ->and($errorLogs[0]['context']['error'])
        ->toBe('Generic runtime error')
        ->and($errorLogs[0]['context']['code'])
        ->toBe(500)
        ->and($errorLogs[0]['context'])
        ->not->toHaveKey('responseBody')
        ->and($errorLogs[0]['context'])
        ->not->toHaveKey('responseHeaders');
});

it('re-throws exception after logging', function () {
    $service = new ConcreteSdkApiServiceForTest();

    $exception = new ApiException('Test error', 500, [], '');
    $operation = function () use ($exception) {
        throw $exception;
    };

    try {
        $service->publicExecuteOperationWithErrorHandling($operation, 'test');
        $caught = false;
    } catch (ApiException $e) {
        $caught = true;
        expect($e)->toBe($exception);
    }

    expect($caught)->toBeTrue();
});

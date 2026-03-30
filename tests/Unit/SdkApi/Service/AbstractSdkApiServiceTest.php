<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
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

    public function publicCreateGuzzleClientHandlerStack(): HandlerStack
    {
        return $this->createGuzzleClientHandlerStack();
    }

    public function publicCreateGuzzleClient(): Client
    {
        return $this->createGuzzleClient();
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

// Tests for createGuzzleClientHandlerStack() and createGuzzleClient()
it('createGuzzleClientHandlerStack returns a HandlerStack', function () {
    $service = new ConcreteSdkApiServiceForTest();
    $stack   = $service->publicCreateGuzzleClientHandlerStack();

    expect($stack)->toBeInstanceOf(HandlerStack::class);
});

it('createGuzzleClient returns a Guzzle Client', function () {
    $service = new ConcreteSdkApiServiceForTest();
    $client  = $service->publicCreateGuzzleClient();

    expect($client)->toBeInstanceOf(Client::class);
});

<?php

/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\SdkApi\Service\CoreApi;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Configuration as CoreApiConfiguration;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

// Concrete implementation for testing
class ConcreteCoreApiServiceForTest extends AbstractCoreApiService
{
    public function publicGetApiConfig(): CoreApiConfiguration
    {
        return $this->getApiConfig();
    }
}

// Tests for getApiConfig()
it('returns Configuration instance based on generated CoreApi Config class', function () {
    TestBootstrapper::hasApiKey('test-key');

    $service = new ConcreteCoreApiServiceForTest();
    $config  = $service->publicGetApiConfig();

    expect($config)->toBeInstanceOf(CoreApiConfiguration::class);
});

it('sets base64-encoded access token from API key', function () {
    TestBootstrapper::hasApiKey('my-secret-key');

    $service = new ConcreteCoreApiServiceForTest();
    $config  = $service->publicGetApiConfig();

    expect($config->getAccessToken())->toBe(base64_encode('my-secret-key'));
});

it('populates user agent in the config with platform info', function () {
    TestBootstrapper::hasApiKey();

    $service   = new ConcreteCoreApiServiceForTest();
    $config    = $service->publicGetApiConfig();
    $userAgent = $config->getUserAgent();
    $pdkVersion = \MyParcelNL\Pdk\Facade\Pdk::get('pdkVersion');

    expect($userAgent)
        ->toContain('MyParcelNL-PDK/' . $pdkVersion)
        ->toContain('php/' . PHP_VERSION);
});

it('config uses default host when environment is production', function () {
    TestBootstrapper::hasAccount();

    factory(AccountSettings::class)
        ->withEnvironment(Config::ENVIRONMENT_PRODUCTION)
        ->store();

    $service = new ConcreteCoreApiServiceForTest();
    $config  = $service->publicGetApiConfig();

    // Default from OpenAPI spec should be used (not explicitly set)
    $defaultConfig = new CoreApiConfiguration();
    expect($config->getHost())->toBe($defaultConfig->getHost());
});

it('sets acceptance host in config when environment is acceptance', function () {
    TestBootstrapper::hasAccount();

    factory(AccountSettings::class)
        ->withEnvironment(Config::ENVIRONMENT_ACCEPTANCE)
        ->store();

    $service = new ConcreteCoreApiServiceForTest();
    $config  = $service->publicGetApiConfig();

    expect($config->getHost())->toBe(Config::API_URL_ACCEPTANCE);
});

it('handles null API key gracefully', function () {
    // No API key set
    $service = new ConcreteCoreApiServiceForTest();
    $config  = $service->publicGetApiConfig();

    // Should still return a config, with base64 of null (empty string essentially)
    expect($config)->toBeInstanceOf(CoreApiConfiguration::class);
    expect($config->getAccessToken())->toBe('');
});

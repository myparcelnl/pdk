<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Api\Service;

use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Service\CapabilitiesApiService;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

beforeEach(function () {
    $this->clientAdapter = $this->createMock(ClientAdapterInterface::class);
    $this->service       = new CapabilitiesApiService($this->clientAdapter);

    // Reset all mocked properties to default values
    mockPdkProperty('capabilitiesServiceUrl', 'https://api.myparcel.nl');
    mockPdkProperty('userAgent', []);
    mockPdkProperty('pdkVersion', '1.0.0');

    // Set default API key for most tests
    TestBootstrapper::hasApiKey('test-api-key');
});

// Base URL configuration tests
it('uses configured service url for base url', function () {
    expect($this->service->getBaseUrl())
        ->toBe('https://api.myparcel.nl');
});

it('uses property if set for base url', function () {
    $expectedUrl = 'https://custom-api.myparcel.nl';
    $this->service->setBaseUrl($expectedUrl);

    expect($this->service->getBaseUrl())
        ->toBe($expectedUrl);
});

it('throws exception if base url is not configured', function () {
    mockPdkProperty('capabilitiesServiceUrl', null);

    expect(fn() => $this->service->getBaseUrl())
        ->toThrow(\RuntimeException::class, 'Capabilities service URL is not configured');
});

// API Key configuration tests
it('injects bearer token in headers', function () {
    $apiKey = 'test-api-key';
    TestBootstrapper::hasApiKey($apiKey);

    $headers = $this->service->getHeaders();

    expect($headers)
        ->toHaveKey('Authorization')
        ->and($headers['Authorization'])
        ->toBe('bearer ' . base64_encode($apiKey));
});

it('throws exception if api key is not configured', function () {
    TestBootstrapper::hasApiKey(null);

    expect(fn() => $this->service->getHeaders())
        ->toThrow(\RuntimeException::class, 'API key is not configured');
});

// User Agent configuration tests
it('includes default user agent in headers', function () {
    mockPdkProperty('userAgent', []);
    mockPdkProperty('pdkVersion', '1.0.0');

    $headers = $this->service->getHeaders();

    expect($headers)
        ->toHaveKey('User-Agent')
        ->and($headers['User-Agent'])
        ->toContain('MyParcelNL-PDK/1.0.0')
        ->and($headers['User-Agent'])
        ->toContain('php/' . PHP_VERSION);
});

it('includes custom user agent in headers', function () {
    mockPdkProperty('userAgent', ['CustomApp' => '1.0.0']);
    mockPdkProperty('pdkVersion', '1.0.0');

    $headers = $this->service->getHeaders();

    expect($headers)
        ->toHaveKey('User-Agent')
        ->and($headers['User-Agent'])
        ->toContain('CustomApp/1.0.0')
        ->and($headers['User-Agent'])
        ->toContain('MyParcelNL-PDK/1.0.0')
        ->and($headers['User-Agent'])
        ->toContain('php/' . PHP_VERSION);
});

it('includes multiple custom user agents in headers', function () {
    mockPdkProperty('userAgent', [
        'CustomApp' => '1.0.0',
        'AnotherApp' => '2.0.0',
    ]);
    mockPdkProperty('pdkVersion', '1.0.0');

    $headers = $this->service->getHeaders();

    expect($headers)
        ->toHaveKey('User-Agent')
        ->and($headers['User-Agent'])
        ->toContain('CustomApp/1.0.0')
        ->and($headers['User-Agent'])
        ->toContain('AnotherApp/2.0.0')
        ->and($headers['User-Agent'])
        ->toContain('MyParcelNL-PDK/1.0.0')
        ->and($headers['User-Agent'])
        ->toContain('php/' . PHP_VERSION);
});

it('handles empty user agent configuration', function () {
    mockPdkProperty('userAgent', []);
    mockPdkProperty('pdkVersion', null);

    $headers = $this->service->getHeaders();

    expect($headers)
        ->toHaveKey('User-Agent')
        ->and($headers['User-Agent'])
        ->toBe('php/' . PHP_VERSION);
});

it('handles empty pdk version configuration', function () {
    mockPdkProperty('userAgent', ['CustomApp' => '1.0.0']);
    mockPdkProperty('pdkVersion', null);

    $headers = $this->service->getHeaders();

    expect($headers)
        ->toHaveKey('User-Agent')
        ->and($headers['User-Agent'])
        ->toContain('CustomApp/1.0.0')
        ->and($headers['User-Agent'])
        ->toContain('php/' . PHP_VERSION);
});

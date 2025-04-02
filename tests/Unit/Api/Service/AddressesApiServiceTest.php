<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Api\Service;

use MyParcelNL\Pdk\Api\Contract\ClientAdapterInterface;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use PHPUnit\Framework\TestCase;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

class AddressesApiServiceTest extends TestCase
{
    /**
     * @var AddressesApiService
     */
    private $service;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ClientAdapterInterface
     */
    private $clientAdapter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientAdapter = $this->createMock(ClientAdapterInterface::class);
        $this->service = new AddressesApiService($this->clientAdapter);

        mockPdkProperty('addressesServiceUrl', 'https://api.myparcel.nl/addresses');
        mockPdkProperty('apiKey', 'test-api-key');
    }

    public function testGetBaseUrlUsesConfiguredServiceUrl(): void
    {
        $expectedUrl = 'https://api.myparcel.nl/addresses';

        $this->assertEquals($expectedUrl, $this->service->getBaseUrl());
    }

    public function testGetBaseUrlUsesPropertyIfSet(): void
    {
        $expectedUrl = 'https://custom-api.myparcel.nl/addresses';
        $this->service->setBaseUrl($expectedUrl);

        $this->assertEquals($expectedUrl, $this->service->getBaseUrl());
    }

    public function testGetBaseUrlThrowsExceptionIfNotConfigured(): void
    {
        mockPdkProperty('addressesServiceUrl', null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Addresses service URL is not configured');

        $this->service->getBaseUrl();
    }

    public function testGetHeadersInjectsBearerToken(): void
    {
        $apiKey = 'test-api-key';
        mockPdkProperty('apiKey', $apiKey);

        $headers = $this->service->getHeaders();

        $this->assertArrayHasKey('Authorization', $headers);
        $this->assertEquals('bearer ' . base64_encode($apiKey), $headers['Authorization']);
    }

    public function testGetHeadersThrowsExceptionIfApiKeyNotConfigured(): void
    {
        mockPdkProperty('apiKey', null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API key is not configured');

        $this->service->getHeaders();
    }

    public function testGetHeadersIncludesUserAgent(): void
    {
        mockPdkProperty('userAgent', []);
        mockPdkProperty('pdkVersion', '1.0.0');

        $headers = $this->service->getHeaders();

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertStringContainsString('MyParcelNL-PDK/1.0.0', $headers['User-Agent']);
        $this->assertStringContainsString('php/' . PHP_VERSION, $headers['User-Agent']);
    }

    public function testGetHeadersIncludesCustomUserAgent(): void
    {
        mockPdkProperty('userAgent', ['CustomApp' => '1.0.0']);
        mockPdkProperty('pdkVersion', '1.0.0');

        $headers = $this->service->getHeaders();

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertStringContainsString('CustomApp/1.0.0', $headers['User-Agent']);
        $this->assertStringContainsString('MyParcelNL-PDK/1.0.0', $headers['User-Agent']);
        $this->assertStringContainsString('php/' . PHP_VERSION, $headers['User-Agent']);
    }

    public function testGetHeadersIncludesMultipleCustomUserAgents(): void
    {
        mockPdkProperty('userAgent', [
            'CustomApp' => '1.0.0',
            'WooCommerce' => '5.0.0',
            'WordPress' => '5.8.0'
        ]);
        mockPdkProperty('pdkVersion', '1.0.0');

        $headers = $this->service->getHeaders();

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertStringContainsString('CustomApp/1.0.0', $headers['User-Agent']);
        $this->assertStringContainsString('WooCommerce/5.0.0', $headers['User-Agent']);
        $this->assertStringContainsString('WordPress/5.8.0', $headers['User-Agent']);
        $this->assertStringContainsString('MyParcelNL-PDK/1.0.0', $headers['User-Agent']);
        $this->assertStringContainsString('php/' . PHP_VERSION, $headers['User-Agent']);
    }

    public function testGetHeadersIncludesEmptyUserAgent(): void
    {
        mockPdkProperty('userAgent', null);
        mockPdkProperty('pdkVersion', '1.0.0');

        $headers = $this->service->getHeaders();

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertStringContainsString('MyParcelNL-PDK/1.0.0', $headers['User-Agent']);
        $this->assertStringContainsString('php/' . PHP_VERSION, $headers['User-Agent']);
    }

    public function testGetHeadersIncludesEmptyPdkVersion(): void
    {
        mockPdkProperty('userAgent', []);
        mockPdkProperty('pdkVersion', null);

        $headers = $this->service->getHeaders();

        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertStringContainsString('MyParcelNL-PDK/unknown', $headers['User-Agent']);
        $this->assertStringContainsString('php/' . PHP_VERSION, $headers['User-Agent']);
    }
} 
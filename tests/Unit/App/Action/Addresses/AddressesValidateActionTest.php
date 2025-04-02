<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Addresses;

use MyParcelNL\Pdk\App\Action\Addresses\AddressesValidateAction;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\Api\Response\ValidateAddressResponse;
use MyParcelNL\Pdk\Api\Request\ProxyRequest;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AddressesValidateActionTest extends TestCase
{
    /**
     * @var AddressesApiService|MockObject
     */
    private $apiService;

    /**
     * @var AddressesValidateAction
     */
    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->apiService = $this->createMock(AddressesApiService::class);
        $this->action = new AddressesValidateAction($this->apiService);
    }

    public function testHandleWithValidAddress(): void
    {
        $request = new Request();
        $request->query->set('postalCode', '1234AB');
        $request->query->set('houseNumber', '1');
        $request->query->set('cc', 'NL');

        $proxyRequest = new ProxyRequest(
            'GET',
            '/validate',
            null,
            [
                'countryCode' => 'NL',
                'postalCode' => '1234AB',
                'houseNumber' => '1'
            ]
        );

        $validateResponse = $this->createMock(ValidateAddressResponse::class);
        $validateResponse->method('isValid')->willReturn(true);

        $this->apiService
            ->expects($this->once())
            ->method('doRequest')
            ->with($proxyRequest, ValidateAddressResponse::class)
            ->willReturn($validateResponse);

        $response = $this->action->handle($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('valid', $responseData);
        $this->assertTrue($responseData['valid']);
    }

    public function testHandleWithInvalidAddress(): void
    {
        $request = new Request();
        $request->query->set('postalCode', '1234AB');
        $request->query->set('houseNumber', '999');
        $request->query->set('cc', 'NL');

        $proxyRequest = new ProxyRequest(
            'GET',
            '/validate',
            null,
            [
                'countryCode' => 'NL',
                'postalCode' => '1234AB',
                'houseNumber' => '999'
            ]
        );

        $validateResponse = $this->createMock(ValidateAddressResponse::class);
        $validateResponse->method('isValid')->willReturn(false);

        $this->apiService
            ->expects($this->once())
            ->method('doRequest')
            ->with($proxyRequest, ValidateAddressResponse::class)
            ->willReturn($validateResponse);

        $response = $this->action->handle($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('valid', $responseData);
        $this->assertFalse($responseData['valid']);
    }

    public function testHandleWithMissingParameters(): void
    {
        $request = new Request();
        $request->query->set('postalCode', '1234AB');

        $proxyRequest = new ProxyRequest(
            'GET',
            '/validate',
            null,
            [
                'countryCode' => 'NL',
                'postalCode' => '1234AB'
            ]
        );

        $validateResponse = $this->createMock(ValidateAddressResponse::class);
        $validateResponse->method('isValid')->willReturn(true);

        $this->apiService
            ->expects($this->once())
            ->method('doRequest')
            ->with($proxyRequest, ValidateAddressResponse::class)
            ->willReturn($validateResponse);

        $response = $this->action->handle($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('valid', $responseData);
        $this->assertTrue($responseData['valid']);
    }
} 
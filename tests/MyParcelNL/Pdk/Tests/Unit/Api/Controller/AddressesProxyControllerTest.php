<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Api\Controller;

use Exception;
use MyParcelNL\Pdk\Api\Controller\AddressesProxyController;
use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\Api\Request\ProxyRequest;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AddressesProxyControllerTest extends TestCase
{
    /**
     * @var AddressesApiService|MockObject
     */
    private $addressesApiService;

    /**
     * @var AddressesProxyController
     */
    private $controller;

    /**
     * @var CorsHandler|MockObject
     */
    private $corsHandler;

    public function testProxyWithAddressesServiceError(): void
    {
        $request = new Request();
        $request->headers->set('Origin', 'https://example.com');

        $proxyRequest = new ProxyRequest('GET', '/addresses', null, [], []);

        $this->corsHandler
            ->expects($this->once())
            ->method('handlePreflight')
            ->with($request)
            ->willReturn(null);

        $this->addressesApiService
            ->expects($this->once())
            ->method('doRequest')
            ->with($proxyRequest)
            ->willThrowException(new Exception('Invalid postal code format'));

        $this->corsHandler
            ->expects($this->once())
            ->method('addCorsHeaders')
            ->with($request, $this->isInstanceOf(Response::class));

        $response = $this->controller->proxy($request, '/addresses');

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid postal code format', $responseData['error']);
    }

    public function testProxyWithCorrectCorsHeaders(): void
    {
        $request = new Request();
        $request->headers->set('Origin', 'https://example.com');

        $proxyRequest = new ProxyRequest('GET', '/addresses', null, [], []);
        $apiResponse  = new Response('{"data": []}', Response::HTTP_OK);

        $this->corsHandler
            ->expects($this->once())
            ->method('handlePreflight')
            ->with($request)
            ->willReturn(null);

        $this->addressesApiService
            ->expects($this->once())
            ->method('doRequest')
            ->with($proxyRequest)
            ->willReturn($apiResponse);

        $this->corsHandler
            ->expects($this->once())
            ->method('addCorsHeaders')
            ->with($request, $this->isInstanceOf(Response::class));

        $response = $this->controller->proxy($request, '/addresses');

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testProxyWithCorsPreflight(): void
    {
        $request = new Request();
        $request->setMethod('OPTIONS');
        $request->headers->set('Origin', 'https://example.com');

        $expectedResponse = new Response('', Response::HTTP_NO_CONTENT);

        $this->corsHandler
            ->expects($this->once())
            ->method('handlePreflight')
            ->with($request)
            ->willReturn($expectedResponse);

        $response = $this->controller->proxy($request, '/test');

        $this->assertSame($expectedResponse, $response);
    }

    public function testProxyWithSuccessfulAddressesResponse(): void
    {
        $request = new Request();
        $request->headers->set('Origin', 'https://example.com');

        $proxyRequest = new ProxyRequest('GET', '/addresses', null, [], []);
        $apiResponse  = new Response(
            json_encode([
                'data' => [
                    [
                        'id'          => 1,
                        'street'      => 'Test Street',
                        'number'      => '123',
                        'postal_code' => '1234 AB',
                        'city'        => 'Test City',
                        'country'     => 'NL',
                    ],
                ],
            ]),
            Response::HTTP_OK
        );

        $this->corsHandler
            ->expects($this->once())
            ->method('handlePreflight')
            ->with($request)
            ->willReturn(null);

        $this->addressesApiService
            ->expects($this->once())
            ->method('doRequest')
            ->with($proxyRequest)
            ->willReturn($apiResponse);

        $this->corsHandler
            ->expects($this->once())
            ->method('addCorsHeaders')
            ->with($request, $this->isInstanceOf(Response::class));

        $response = $this->controller->proxy($request, '/addresses');

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertIsArray($responseData['data']);
        $this->assertCount(1, $responseData['data']);
        $this->assertArrayHasKey('street', $responseData['data'][0]);
        $this->assertArrayHasKey('number', $responseData['data'][0]);
        $this->assertArrayHasKey('postal_code', $responseData['data'][0]);
        $this->assertArrayHasKey('city', $responseData['data'][0]);
        $this->assertArrayHasKey('country', $responseData['data'][0]);
    }

    public function testProxyWithUnauthorizedOrigin(): void
    {
        $request = new Request();
        $request->headers->set('Origin', 'https://unauthorized.com');

        $this->corsHandler
            ->expects($this->once())
            ->method('handlePreflight')
            ->with($request)
            ->willReturn(null);

        $response = $this->controller->proxy($request, '/test');

        $this->assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        $this->assertEquals('Unauthorized origin', $response->getContent());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->addressesApiService = $this->createMock(AddressesApiService::class);
        $this->corsHandler         = $this->createMock(CorsHandler::class);

        $this->controller = new AddressesProxyController(
            $this->addressesApiService,
            $this->corsHandler
        );
    }
}

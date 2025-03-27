<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Api\Controller;

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
     * @var CorsHandler|MockObject
     */
    private $corsHandler;

    /**
     * @var AddressesProxyController
     */
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addressesApiService = $this->createMock(AddressesApiService::class);
        $this->corsHandler = $this->createMock(CorsHandler::class);

        $this->controller = new AddressesProxyController(
            $this->addressesApiService,
            $this->corsHandler
        );
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

    public function testProxyWithSuccessfulRequest(): void
    {
        $request = new Request();
        $request->headers->set('Origin', 'https://example.com');

        $proxyRequest = new ProxyRequest('GET', '/test', null, [], []);
        $apiResponse = new Response('{"data": "test"}', Response::HTTP_OK);
        
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

        $response = $this->controller->proxy($request, '/test');

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals('{"data": "test"}', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testProxyWithError(): void
    {
        $request = new Request();
        $request->headers->set('Origin', 'https://example.com');

        $proxyRequest = new ProxyRequest('GET', '/test', null, [], []);
        
        $this->corsHandler
            ->expects($this->once())
            ->method('handlePreflight')
            ->with($request)
            ->willReturn(null);

        $this->addressesApiService
            ->expects($this->once())
            ->method('doRequest')
            ->with($proxyRequest)
            ->willThrowException(new \Exception('API Error'));

        $this->corsHandler
            ->expects($this->once())
            ->method('addCorsHeaders')
            ->with($request, $this->isInstanceOf(Response::class));

        $response = $this->controller->proxy($request, '/test');

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals('{"error":"API Error"}', $response->getContent());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }
} 
<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Addresses;

use MyParcelNL\Pdk\Api\Contract\ApiResponseInterface;
use MyParcelNL\Pdk\Api\Request\ProxyRequest;
use MyParcelNL\Pdk\Api\Response\ValidateAddressResponse;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\App\Action\Addresses\AddressesValidateAction;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

beforeEach(function () {
    $this->apiService = $this->createMock(AddressesApiService::class);
    $this->action = new AddressesValidateAction($this->apiService);
});

it('handles valid address', function () {
    $request = new Request([
        'postalCode' => '1234AB',
        'cc' => 'NL',
    ]);

    $mockResponse = $this->createMock(ValidateAddressResponse::class);
    $mockResponse->method('isValid')->willReturn(true);

    $this->apiService
        ->expects($this->once())
        ->method('doRequest')
        ->with(
            $this->callback(function (ProxyRequest $proxyRequest) {
                return $proxyRequest->getMethod() === 'GET' &&
                    $proxyRequest->getPath() === '/validate' &&
                    $proxyRequest->getQueryString() === 'countryCode=NL&postalCode=1234AB';
            }),
            ValidateAddressResponse::class
        )
        ->willReturn($mockResponse);

    $response = $this->action->handle($request);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
        ->toBe(200)
        ->and(json_decode($response->getContent(), true))
        ->toBe([
            'data' => [
                'valid' => true,
            ],
        ]);
});

it('handles invalid address', function () {
    $request = new Request([
        'postalCode' => '1234AB',
        'cc' => 'NL',
    ]);

    $mockResponse = $this->createMock(ValidateAddressResponse::class);
    $mockResponse->method('isValid')->willReturn(false);

    $this->apiService
        ->expects($this->once())
        ->method('doRequest')
        ->with(
            $this->callback(function (ProxyRequest $proxyRequest) {
                return $proxyRequest->getMethod() === 'GET' &&
                    $proxyRequest->getPath() === '/validate' &&
                    $proxyRequest->getQueryString() === 'countryCode=NL&postalCode=1234AB';
            }),
            ValidateAddressResponse::class
        )
        ->willReturn($mockResponse);

    $response = $this->action->handle($request);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
        ->toBe(200)
        ->and(json_decode($response->getContent(), true))
        ->toBe([
            'data' => [
                'valid' => false,
            ],
        ]);
});

it('handles missing parameters', function () {
    $request = new Request([
        'postalCode' => '1234AB',
    ]);

    $mockResponse = $this->createMock(ValidateAddressResponse::class);
    $mockResponse->method('isValid')->willReturn(true);

    $this->apiService
        ->expects($this->once())
        ->method('doRequest')
        ->with(
            $this->callback(function (ProxyRequest $proxyRequest) {
                return $proxyRequest->getMethod() === 'GET' &&
                    $proxyRequest->getPath() === '/validate' &&
                    $proxyRequest->getQueryString() === 'postalCode=1234AB';
            }),
            ValidateAddressResponse::class
        )
        ->willReturn($mockResponse);

    $response = $this->action->handle($request);

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
        ->toBe(200)
        ->and(json_decode($response->getContent(), true))
        ->toBe([
            'data' => [
                'valid' => true,
            ],
        ]);
});

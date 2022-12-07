<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use MyParcelNL\Pdk\Api\Response\ApiResponse;
use MyParcelNL\Pdk\Api\Response\ClientResponse;

it('can be instantiated', function () {
    $response = new ApiResponse(new ClientResponse(null, 201));

    expect($response)->toBeInstanceOf(ApiResponse::class);
});

it('can get the body', function () {
    $response = new ApiResponse(new ClientResponse(null, 201));

    expect($response->getBody())->toBe(null);
});

it('can get the status code', function () {
    $response = new ApiResponse(new ClientResponse(null, 201));

    expect($response->getStatusCode())->toBe(201);
});

it('can get errors', function () {
    $response = new ApiResponse(new ClientResponse(null, 401));

    expect($response->getErrors())->toBe([]);
});

it('can check if it is an error response', function () {
    $response = new ApiResponse(new ClientResponse(null, 201));

    expect($response->isErrorResponse())->toBeFalse();
});

it('returns true when response is ok', function () {
    $response = new ApiResponse(new ClientResponse(null, 201));

    expect($response->isOkResponse())->toBeTrue();
});

it('returns false when response is not ok', function () {
    $response = new ApiResponse(new ClientResponse(null, 400));

    expect($response->isOkResponse())->toBeFalse();
});

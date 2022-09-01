<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Response\ClientResponse;
use MyParcelNL\Pdk\Tests\Mocks\MockApiResponse;
use Symfony\Component\HttpFoundation\Response;

it('tests response', function () {
    $response = new MockApiResponse(new ClientResponse('{}', Response::HTTP_UNPROCESSABLE_ENTITY));

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->and($response->isErrorResponse())
        ->toBe(true)
        ->and($response->isOkResponse())
        ->toBe(false)
        ->and($response->getErrors())
        ->toBeArray();
});

<?php

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use Symfony\Component\HttpFoundation\Response;

it('can get the body', function () {
    $exampleJson = json_encode([
        'results' => [
            'street' => 'Street',
            'number' => '123',
            'postal_code' => '1234AB',
            'city' => 'City',
            'country_code' => 'NL',
        ]
    ]);
    $response = new AddressResponse(new ClientResponse($exampleJson, 201));

    expect($response->getBody())->toBe($exampleJson);
});

it('can get the symfony response', function () {
    $exampleJson = json_encode([
        'results' => [
            'street' => 'Street',
            'number' => '123',
            'postal_code' => '1234AB',
            'city' => 'City',
            'country_code' => 'NL',
        ]
    ]);

    $response = new AddressResponse(new ClientResponse($exampleJson, 200));

    expect($response->getSymfonyResponse())->toBeInstanceOf(Response::class);
    expect($response->getSymfonyResponse()->getStatusCode())->toBe(200);
    expect($response->getSymfonyResponse()->getContent())->toBe($exampleJson);
});

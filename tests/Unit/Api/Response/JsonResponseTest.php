<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Api\Response;

use ReflectionMethod;

it('creates a pdk json response through the factory method', function () {
    $response = JsonResponse::create([
        'success' => true,
    ], 201, [
        'X-Test' => '1',
    ]);

    expect($response->getStatusCode())->toBe(201);
    expect($response->headers->get('X-Test'))->toBe('1');
    expect(json_decode($response->getContent(), true))->toBe([
        'data' => [
            'success' => true,
        ],
    ]);
});

it('keeps the create factory parameters untyped for symfony compatibility', function () {
    $method = new ReflectionMethod(JsonResponse::class, 'create');

    foreach ($method->getParameters() as $parameter) {
        expect($parameter->hasType())->toBeFalse();
    }
});

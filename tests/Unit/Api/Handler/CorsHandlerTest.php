<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Api\Handler;

use MyParcelNL\Pdk\Api\Handler\CorsHandler;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function MyParcelNL\Pdk\Tests\mockPdkProperty;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('uses allowedProxyOrigins for actual request headers', function () {
    mockPdkProperty('allowedProxyOrigins', ['https://shop.example']);
    mockPdkProperty('allowedProxyHosts', ['https://fallback.example']);

    $handler  = new CorsHandler();
    $request  = Request::create('/', 'GET', [], [], [], ['HTTP_ORIGIN' => 'https://shop.example']);
    $response = $handler->addCorsHeaders($request, new Response('', 200));

    expect($response->headers->get('Access-Control-Allow-Origin'))
        ->toBe('https://shop.example');
});

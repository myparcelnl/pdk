<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Request\Debug;

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns correct headers', function () {
    $request = new SwitchToAcceptanceApiEndpointRequest();
    
    $headers = $request->getHeaders();
    
    expect($headers)->toBeArray()
        ->and($headers)->toHaveKey('Accept')
        ->and($headers['Accept'])->toBe('application/json');
});

it('returns POST method', function () {
    $request = new SwitchToAcceptanceApiEndpointRequest();
    
    $method = $request->getMethod();
    
    expect($method)->toBe(HttpRequest::METHOD_POST);
});

<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Request\Request;

it('creates empty request', function () {
    $request = new Request();

    expect($request->getBody())
        ->toBe(null)
        ->and($request->getHeaders())
        ->toBe([])
        ->and($request->getMethod())
        ->toBe('GET')
        ->and($request->getPath())
        ->toBe('');
});

it('fills request with properties', function () {
    $request = new Request([
        'body'       => 'body',
        'headers'    => ['header' => 'value'],
        'method'     => 'POST',
        'path'       => '/path',
        'parameters' => ['param' => 'value'],
    ]);

    expect($request->getBody())
        ->toBe('body')
        ->and($request->getHeaders())
        ->toBe(['header' => 'value'])
        ->and($request->getMethod())
        ->toBe('POST')
        ->and($request->getPath())
        ->toBe('/path')
        ->and($request->getQueryString())
        ->toBe('param=value');
});

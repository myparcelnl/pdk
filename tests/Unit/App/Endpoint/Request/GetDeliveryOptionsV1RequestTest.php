<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint\Contract;

use MyParcelNL\Pdk\App\Endpoint\Request\GetDeliveryOptionsV1Request;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Request;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('requires an order id', function () {
    $httpRequest = new Request();
    $request     = new GetDeliveryOptionsV1Request($httpRequest);

    expect($request->validate())->toBeFalse();
    expect($request->getValidationErrors())->toHaveKey('orderId');
});

it('extracts the order id as a string', function () {
    $httpRequest = new Request(['orderId' => '123']);
    $request     = new GetDeliveryOptionsV1Request($httpRequest);

    expect($request->validate())->toBeTrue();
    expect($request->getOrderId())->toBe('123');
});

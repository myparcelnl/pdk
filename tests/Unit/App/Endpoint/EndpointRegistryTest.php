<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Endpoint;

use MyParcelNL\Pdk\App\Endpoint\EndpointRegistry;
use MyParcelNL\Pdk\App\Endpoint\Handler\GetDeliveryOptionsEndpoint;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('provides DELIVERY_OPTIONS constant', function () {
    expect(EndpointRegistry::DELIVERY_OPTIONS)->toBe(GetDeliveryOptionsEndpoint::class);
});

it('returns all registered endpoint handler classes', function () {
    $allEndpoints = EndpointRegistry::all();

    expect($allEndpoints)->toBeArray();
    expect($allEndpoints)->toContain(GetDeliveryOptionsEndpoint::class);
});

it('all() contains the same classes as constants', function () {
    $allEndpoints = EndpointRegistry::all();

    expect($allEndpoints)->toContain(EndpointRegistry::DELIVERY_OPTIONS);
});

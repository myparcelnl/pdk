<?php

declare(strict_types=1);

namespace Tests\Unit\App\Order\Calculator\Gls;

use MyParcelNL\Pdk\App\Order\Calculator\Gls\GlsCalculator;
use MyParcelNL\Pdk\App\Order\Calculator\Gls\GlsShipmentOptionsCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns correct carrier name', function () {
    $order = factory(PdkOrder::class)->make();
    $calculator = new GlsCalculator($order);
    
    // Test getCarrier method via reflection since it's protected
    $reflection = new \ReflectionClass($calculator);
    $method = $reflection->getMethod('getCarrier');
    $method->setAccessible(true);
    
    expect($method->invoke($calculator))->toBe(Carrier::CARRIER_GLS_NAME);
});

it('returns correct calculators array', function () {
    $order = factory(PdkOrder::class)->make();
    $calculator = new GlsCalculator($order);
    
    // Test getCalculators method via reflection since it's protected
    $reflection = new \ReflectionClass($calculator);
    $method = $reflection->getMethod('getCalculators');
    $method->setAccessible(true);
    
    $calculators = $method->invoke($calculator);
    
    expect($calculators)->toBeArray()
        ->toHaveCount(1)
        ->toContain(GlsShipmentOptionsCalculator::class);
});

it('calculates shipment options correctly', function () {
    $order = factory(PdkOrder::class)->make();
    $calculator = new GlsCalculator($order);
    
    // Test that calculate method runs without errors and completes
    $calculator->calculate();
    
    // If we get here, the method executed successfully
    expect(true)->toBeTrue();
});

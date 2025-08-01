<?php

declare(strict_types=1);

namespace Tests\Unit\App\Order\Calculator\Gls;

use MyParcelNL\Pdk\App\Order\Calculator\Gls\GlsInsuranceCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('always sets insurance to 10000 (100 euro)', function () {
    $order = factory(PdkOrder::class)->withDeliveryOptions(
        factory(DeliveryOptions::class)->withShipmentOptions(
            factory(ShipmentOptions::class)->withInsurance(0) // Start with no insurance
        )
    )->make();
    
    $calculator = new GlsInsuranceCalculator($order);
    $calculator->calculate();
    
    expect($order->deliveryOptions->shipmentOptions->insurance)->toBe(10000);
});

it('overwrites existing insurance value', function () {
    $order = factory(PdkOrder::class)->withDeliveryOptions(
        factory(DeliveryOptions::class)->withShipmentOptions(
            factory(ShipmentOptions::class)->withInsurance(50000) // Start with 500 euro
        )
    )->make();
    
    $calculator = new GlsInsuranceCalculator($order);
    $calculator->calculate();
    
    expect($order->deliveryOptions->shipmentOptions->insurance)->toBe(10000);
});

it('sets insurance even when null', function () {
    $order = factory(PdkOrder::class)->withDeliveryOptions(
        factory(DeliveryOptions::class)->withShipmentOptions(
            factory(ShipmentOptions::class)->withInsurance(null) // Start with null
        )
    )->make();
    
    $calculator = new GlsInsuranceCalculator($order);
    $calculator->calculate();
    
    expect($order->deliveryOptions->shipmentOptions->insurance)->toBe(10000);
});

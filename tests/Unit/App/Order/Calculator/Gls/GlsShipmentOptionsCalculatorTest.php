<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\App\Order\Calculator\Gls\GlsShipmentOptionsCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('keeps signature disabled for Netherlands', function () {
    $order = new PdkOrder([
        'deliveryOptions' => new DeliveryOptions([
            'shipmentOptions' => new ShipmentOptions([
                'signature' => TriStateService::DISABLED,
                'insurance' => TriStateService::DISABLED,
            ]),
        ]),
        'shippingAddress' => [
            'cc' => 'NL', // Netherlands (local country)
        ],
    ]);

    $calculator = new GlsShipmentOptionsCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->shipmentOptions->signature)
        ->toBe(TriStateService::DISABLED);
    expect($order->deliveryOptions->shipmentOptions->insurance)
        ->toBe(TriStateService::DISABLED);
});

it('enables signature automatically for EU countries (Germany)', function () {
    $order = new PdkOrder([
        'deliveryOptions' => new DeliveryOptions([
            'shipmentOptions' => new ShipmentOptions([
                'signature' => TriStateService::DISABLED,
                'insurance' => TriStateService::DISABLED,
            ]),
        ]),
        'shippingAddress' => [
            'cc' => 'DE',
        ],
    ]);

    $calculator = new GlsShipmentOptionsCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->shipmentOptions->signature)
        ->toBe(TriStateService::ENABLED);
    expect($order->deliveryOptions->shipmentOptions->insurance)
        ->toBe(TriStateService::ENABLED);
});

it('enables signature automatically for EU countries (Belgium)', function () {
    $order = new PdkOrder([
        'deliveryOptions' => new DeliveryOptions([
            'shipmentOptions' => new ShipmentOptions([
                'signature' => TriStateService::DISABLED,
                'insurance' => TriStateService::DISABLED,
            ]),
        ]),
        'shippingAddress' => [
            'cc' => 'BE',
        ],
    ]);

    $calculator = new GlsShipmentOptionsCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->shipmentOptions->signature)
        ->toBe(TriStateService::ENABLED);
    expect($order->deliveryOptions->shipmentOptions->insurance)
        ->toBe(TriStateService::ENABLED);
});

it('enables insurance when signature is manually enabled in Netherlands', function () {
    $order = new PdkOrder([
        'deliveryOptions' => new DeliveryOptions([
            'shipmentOptions' => new ShipmentOptions([
                'signature' => TriStateService::ENABLED, // Manually enabled
                'insurance' => TriStateService::DISABLED,
            ]),
        ]),
        'shippingAddress' => [
            'cc' => 'NL',
        ],
    ]);

    $calculator = new GlsShipmentOptionsCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->shipmentOptions->signature)
        ->toBe(TriStateService::ENABLED);
    expect($order->deliveryOptions->shipmentOptions->insurance)
        ->toBe(TriStateService::ENABLED); // Should be auto-enabled due to signature
});

it('handles null country code gracefully', function () {
    $order = new PdkOrder([
        'deliveryOptions' => new DeliveryOptions([
            'shipmentOptions' => new ShipmentOptions([
                'signature' => TriStateService::DISABLED,
                'insurance' => TriStateService::DISABLED,
            ]),
        ]),
        'shippingAddress' => [
            'cc' => null,
        ],
    ]);

    $calculator = new GlsShipmentOptionsCalculator($order);
    $calculator->calculate();

    // Should not crash and leave options as-is
    expect($order->deliveryOptions->shipmentOptions->signature)
        ->toBe(TriStateService::DISABLED);
    expect($order->deliveryOptions->shipmentOptions->insurance)
        ->toBe(TriStateService::DISABLED);
});

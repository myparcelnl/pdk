<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\App\Order\Calculator\DhlForYou\DhlForYouDeliveryTypeCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('disables onlyRecipient for pickup delivery type for DHL For You', function () {
    $order = new PdkOrder([
        'deliveryOptions' => new DeliveryOptions([
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
            'shipmentOptions' => new ShipmentOptions([
                'onlyRecipient' => TriStateService::ENABLED,
            ]),
        ]),
        'shippingAddress' => [
            'cc' => 'NL',
        ],
    ]);

    $calculator = new DhlForYouDeliveryTypeCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->shipmentOptions->onlyRecipient)
        ->toBe(TriStateService::DISABLED);
});

it('sets deliveryType to standard when evening delivery is selected for a non-local country', function () {
    $order = new PdkOrder([
        'deliveryOptions' => new DeliveryOptions([
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
            'shipmentOptions' => new ShipmentOptions([]),
        ]),
        'shippingAddress' => [
            'cc' => 'FR', 
        ],
    ]);

    $calculator = new DhlForYouDeliveryTypeCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->deliveryType)
        ->toBe(DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME);
});

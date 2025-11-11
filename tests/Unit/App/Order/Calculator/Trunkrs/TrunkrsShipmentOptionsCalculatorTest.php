<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Order\Calculator\Trunkrs\TrunkrsShipmentOptionsCalculator;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('enables signature and only recipient when age check is enabled', function () {
    $order = new PdkOrder([
        'deliveryOptions' => new DeliveryOptions([
            'shipmentOptions' => new ShipmentOptions([
                'ageCheck'      => TriStateService::ENABLED,
                'signature'     => TriStateService::DISABLED,
                'onlyRecipient' => TriStateService::DISABLED,
            ]),
        ]),
    ]);

    $calculator = new TrunkrsShipmentOptionsCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->shipmentOptions->signature)
        ->toBe(TriStateService::ENABLED);
    expect($order->deliveryOptions->shipmentOptions->onlyRecipient)
        ->toBe(TriStateService::ENABLED);
});

it('does nothing when age check is not enabled', function () {
    $order = new PdkOrder([
        'deliveryOptions' => new DeliveryOptions([
            'shipmentOptions' => new ShipmentOptions([
                'ageCheck'      => TriStateService::DISABLED,
                'signature'     => TriStateService::DISABLED,
                'onlyRecipient' => TriStateService::DISABLED,
            ]),
        ]),
    ]);

    $calculator = new TrunkrsShipmentOptionsCalculator($order);
    $calculator->calculate();

    expect($order->deliveryOptions->shipmentOptions->signature)
        ->toBe(TriStateService::DISABLED);
    expect($order->deliveryOptions->shipmentOptions->onlyRecipient)
        ->toBe(TriStateService::DISABLED);
});



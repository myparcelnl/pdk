<?php
/** @noinspection PhpUndefinedMethodInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('can hold and expose data', function () {
    $shipment = new Shipment([
        'carrier'         => new Carrier(['carrier' => ['name' => Carrier::CARRIER_POSTNL_NAME]]),
        'sender'          => new Address(),
        'recipient'       => new Address(),
        'deliveryOptions' => new DeliveryOptions(),
    ]);

    expect($shipment->getCarrier())
        ->toBeInstanceOf(Carrier::class)
        ->and($shipment->getRecipient())
        ->toBeInstanceOf(Address::class)
        ->and($shipment->getSender())
        ->toBeInstanceOf(Address::class)
        ->and($shipment->getDeliveryOptions())
        ->toBeInstanceOf(DeliveryOptions::class);
});

it('passes carrier to delivery options', function (string $carrierName) {
    $shipment = new Shipment([
        'carrier'         => new Carrier(['name' => $carrierName]),
        'deliveryOptions' => new DeliveryOptions([
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
            'shipmentOptions' => [
                'signature' => true,
            ],
        ]),
    ]);

    $deliveryOptions = $shipment->deliveryOptions;
    expect($deliveryOptions ? $deliveryOptions->carrier->name : null)->toEqual($carrierName);
})->with('carrierNames');

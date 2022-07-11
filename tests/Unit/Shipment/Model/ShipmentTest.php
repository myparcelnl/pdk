<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Shipment\Model\Options\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierBpost;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierDPD;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierInstabox;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;

it('warns carrier must be set', function () {
    new Shipment();
})->throws(InvalidArgumentException::class);

it('sets carrier correctly', function () {
    expect((new Shipment(['carrier' => 1]))->carrier)
        ->toBeInstanceOf(CarrierPostNL::class)
        ->and((new Shipment(['carrier' => 'bpost']))->carrier)
        ->toBeInstanceOf(CarrierBpost::class)
        ->and((new Shipment(['carrier' => new CarrierInstabox()]))->carrier)
        ->toBeInstanceOf(CarrierInstabox::class)
        ->and((new Shipment(['carrier' => CarrierDPD::class]))->carrier)
        ->toBeInstanceOf(CarrierDPD::class);
});

it('can hold and expose data', function () {
    $shipment = new Shipment([
        'carrier'         => new CarrierPostNL(),
        'sender'          => new Address(),
        'recipient'       => new Address(),
        'deliveryOptions' => new DeliveryOptions(),
    ]);

    expect($shipment->getCarrier())
        ->toBeInstanceOf(CarrierPostNL::class)
        ->and($shipment->getRecipient())
        ->toBeInstanceOf(Address::class)
        ->and($shipment->getSender())
        ->toBeInstanceOf(Address::class)
        ->and($shipment->getDeliveryOptions())
        ->toBeInstanceOf(DeliveryOptions::class);
});

it('passes carrier to delivery options', function () {
    $shipment = new Shipment([
        'carrier'         => new CarrierPostNL(),
        'deliveryOptions' => new DeliveryOptions([
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
            'shipmentOptions' => [
                'signature' => true,
            ],
        ]),
    ]);

    expect(
        $shipment
            ->getDeliveryOptions()
            ->getCarrier()
    )
        ->toBeInstanceOf(CarrierPostNL::class);
});

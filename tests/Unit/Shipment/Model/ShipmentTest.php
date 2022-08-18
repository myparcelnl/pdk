<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

beforeEach(function () {
    PdkFactory::create(MockPdkConfig::create());
});

it('sets carrier correctly', function ($carrier, string $expectedName) {
    $carrier = is_callable($carrier) ? $carrier() : $carrier;

    expect((new Shipment(['carrier' => $carrier]))->carrier->name)
        ->toBe($expectedName);
})->with([
    'carrier id'      => [
        'carrier'      => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
        'expectedName' => CarrierOptions::CARRIER_POSTNL_NAME,
    ],
    'carrier name'    => [
        'carrier'      => ['name' => CarrierOptions::CARRIER_INSTABOX_NAME],
        'expectedName' => CarrierOptions::CARRIER_INSTABOX_NAME,
    ],
    'subscription id' => [
        'carrier'      => ['subscriptionId' => MockConfig::ID_CUSTOM_SUBSCRIPTION_DPD],
        'expectedName' => CarrierOptions::CARRIER_DPD_NAME,
    ],
    'carrier class'   => [
        'carrier'      => function () { return new CarrierOptions(['name' => CarrierOptions::CARRIER_INSTABOX_NAME]); },
        'expectedName' => CarrierOptions::CARRIER_INSTABOX_NAME,
    ],
]);

it('can hold and expose data', function () {
    $shipment = new Shipment([
        'carrier'         => new CarrierOptions(['name' => CarrierOptions::CARRIER_POSTNL_NAME]),
        'sender'          => new Address(),
        'recipient'       => new Address(),
        'deliveryOptions' => new DeliveryOptions(),
    ]);

    expect($shipment->getCarrier())
        ->toBeInstanceOf(CarrierOptions::class)
        ->and($shipment->getRecipient())
        ->toBeInstanceOf(Address::class)
        ->and($shipment->getSender())
        ->toBeInstanceOf(Address::class)
        ->and($shipment->getDeliveryOptions())
        ->toBeInstanceOf(DeliveryOptions::class);
});

it('passes carrier to delivery options', function () {
    $shipment = new Shipment([
        'carrier'         => new CarrierOptions(['name' => CarrierOptions::CARRIER_POSTNL_NAME]),
        'deliveryOptions' => new DeliveryOptions([
            'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
            'shipmentOptions' => [
                'signature' => true,
            ],
        ]),
    ]);

    $deliveryOptions = $shipment->getDeliveryOptions();
    expect($deliveryOptions ? $deliveryOptions->getCarrier() : null)->toEqual(CarrierOptions::CARRIER_POSTNL_NAME);
});

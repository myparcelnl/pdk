<?php

namespace MyParcelNL\Pdk\Service;

use MyParcelNL\Pdk\Shipment\Model\Options\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Options\PickupLocation;
use MyParcelNL\Pdk\Shipment\Model\Options\ShipmentOptions;

$dataset = [
    '0' => [
        'deliveryOptions' => [
            new DeliveryOptions([
                'carrier'      => 'bloemkool',
                'date'         => '11-07-2022',
                'deliveryType' => 'standard',
                'packageType'  => 'mailbox',
            ]),
        ],
        'expectation'     => [
            'carrier'         => 'bloemkool',
            'date'            => '11-07-2022',
            'deliveryType'    => 'standard',
            'packageType'     => 'mailbox',
            'shipmentOptions' => [
            ],
            'pickupLocation'  => null,
        ],
    ],

    '1' => [
        'deliveryOptions' => [
            new DeliveryOptions([
                'carrier'         => 'postnl',
                'date'            => '11-07-2022',
                'deliveryType'    => 'standard',
                'packageType'     => 'package',
                'shipmentOptions' => new ShipmentOptions([
                    'signature' => false,
                    'insurance' => 500,
                    'ageCheck'  => true,
                ]),
                'pickupLocation'  => null,
            ]),
            new DeliveryOptions([
                'carrier'         => 'postnl',
                'date'            => null,
                'deliveryType'    => null,
                'packageType'     => 'mailbox',
                'shipmentOptions' => new ShipmentOptions([
                    'signature' => true,
                    'insurance' => 0,
                    'ageCheck'  => false,
                ]),
                'pickupLocation'  => null,
            ]),
        ],
        'expectation'     => [
            'carrier'         => 'postnl',
            'date'            => '11-07-2022',
            'deliveryType'    => 'standard',
            'packageType'     => 'mailbox',
            'shipmentOptions' => [
                'signature' => true,
                'insurance' => 0,
                'ageCheck'  => false,
            ],
            'pickupLocation'  => null,
        ],
    ],

    '2' => [
        'deliveryOptions' => [
            new DeliveryOptions([
                'carrier'         => 'instabox',
                'deliveryType'    => 'standard',
                'shipmentOptions' => new ShipmentOptions([
                    'signature' => null,
                    'insurance' => null,
                    'ageCheck'  => null,
                ]),
                'pickupLocation'  => new PickupLocation([
                    'cc'              => 'NL',
                    'city'            => 'Hoofddorp',
                    'locationCode'    => '123456',
                    'locationName'    => 'MyParcel',
                    'number'          => '31',
                    'postalCode'      => '2132 JE',
                    'retailNetworkId' => '1',
                    'street'          => 'Antareslaan',
                ]),
            ]),
            new DeliveryOptions([
                'carrier'         => 'instabox',
                'date'            => null,
                'deliveryType'    => null,
                'packageType'     => 'letter',
                'shipmentOptions' => new ShipmentOptions([
                    'signature' => false,
                    'insurance' => 500,
                    'ageCheck'  => true,
                ]),
            ]),
        ],
        'expectation'     => [
            'carrier'         => 'instabox',
            'date'            => null,
            'deliveryType'    => 'standard',
            'packageType'     => 'letter',
            'shipmentOptions' => [
                'signature' => false,
                'insurance' => 500,
                'ageCheck'  => true,
            ],
            'pickupLocation'  => [
                'cc'              => 'NL',
                'city'            => 'Hoofddorp',
                'locationCode'    => '123456',
                'locationName'    => 'MyParcel',
                'number'          => '31',
                'postalCode'      => '2132 JE',
                'retailNetworkId' => '1',
                'street'          => 'Antareslaan',
            ],
        ],
    ],
];

it('is a instance of DeliveryOptions', function () {
    expect(
        DeliveryOptionsMerger::create(
            new DeliveryOptions([])
        )
    )->toBeInstanceOf(DeliveryOptions::class);
});

it('checks if result has correct values', function ($input) {

    expect($input->carrier)
        ->toBeString()
        ->and($input->shipmentOptions->insurance)
        ->toBeInt()
        ->toBeNumeric()
        ->and($input->shipmentOptions->ageCheck)
        ->toBeBool()
        ->and($input->shipmentOptions->signature)
        ->toBeBool()
        ->and($input->pickupLocation)
        ->toBeNull()
        ->and($input->shipmentOptions)
        ->toBeObject();

})->with(
    [
        '0' =>
            [
                'input' => new DeliveryOptions([
                    'carrier' => 'postnl',
                    'shipmentOptions' => new ShipmentOptions([
                        'insurance' => 500,
                        'ageCheck'  => true,
                        'signature' => false,
                    ]),
                ]),
            ],
    ]
);

it('checks if it merges correctly', function ($deliveryOptions, $expectation) {
    $result = DeliveryOptionsMerger::create(...$deliveryOptions);

    expect($result->toArray())->toEqual($expectation);
})->with($dataset);




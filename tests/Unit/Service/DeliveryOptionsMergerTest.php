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
                'packageType'  => 'package',
            ]),
        ],
        'expectation'     => [
            'carrier'          => 'bloemkool',
            'date'             => '11-07-2022',
            'delivery_type'    => 'standard',
            'package_type'     => 'package',
            'shipment_options' => [
                'age_check'         => null,
                'insurance'         => null,
                'label_description' => null,
                'large_format'      => null,
                'only_recipient'    => null,
                'return'            => null,
                'same_day_delivery' => null,
                'signature'         => null,
            ],
            'pickup_location'  => null,
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
            'carrier'          => 'postnl',
            'date'             => '11-07-2022',
            'delivery_type'    => 'standard',
            'package_type'     => 'mailbox',
            'shipment_options' => [
                'signature' => true,
                'insurance' => 0,
                'age_check' => false,
            ],
            'pickup_location'  => null,
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
            'carrier'          => 'instabox',
            'date'             => null,
            'delivery_type'    => 'standard',
            'package_type'     => 'letter',
            'shipment_options' => [
                'signature' => false,
                'insurance' => 500,
                'age_check' => true,
            ],
            'pickup_location'  => [
                'cc'                => 'NL',
                'city'              => 'Hoofddorp',
                'location_code'     => '123456',
                'location_name'     => 'MyParcel',
                'number'            => '31',
                'postal_code'       => '2132 JE',
                'retail_network_id' => '1',
                'street'            => 'Antareslaan',
            ],
        ],
    ],
];

it('is a instance of DeliveryOptions', function () {
    expect(DeliveryOptionsMerger::create(
        new DeliveryOptions([])
    ))->toBeInstanceOf(DeliveryOptions::class);
});

it('checks if it merges correctly', function ($deliveryOptions, $expectation) {
    $result = DeliveryOptionsMerger::create(...$deliveryOptions);
    expect($result->toArray())->toEqual($expectation);
})->with($dataset);

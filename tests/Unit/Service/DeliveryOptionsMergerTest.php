<?php

namespace MyParcelNL\Pdk\Service;

use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\AbstractDeliveryOptionsAdapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\ShipmentOptionsV3Adapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\PickupLocationV3Adapter;
use MyParcelNL\Sdk\src\Adapter\DeliveryOptions\DeliveryOptionsV3Adapter;
use MyParcelNL\Sdk\src\Exception\ValidationException;

const DELIVERY_OPTIONS = [
    [
        'carrier'         => 'postnl',
        'date'            => '11-07-2022',
        'deliveryType'    => 'standard',
        'packageType'     => 'package',
        'isPickup'        => false,
        'pickupLocation'  => null,
        'shipmentOptions' => [
            'signature'         => true,
            'insurance'         => 500,
            'age_check'         => true,
            'only_recipient'    => null,
            'return'            => false,
            'same_day_delivery' => null,
            'large_format'      => null,
            'label_description' => null,
        ],
    ],
    [
        'carrier'         => '',
        'date'            => '',
        'deliveryType'    => '',
        'packageType'     => '',
        'isPickup'        => '',
        'pickupLocation'  => '',
        'shipmentOptions' => [
            'signature'         => null,
            'insurance'         => null,
            'age_check'         => false,
            'only_recipient'    => null,
            'return'            => false,
            'same_day_delivery' => null,
            'large_format'      => null,
            'label_description' => null,
        ],
    ],
];

const CARRIER = [
    ['carrier' => 'postnl'],
    ['carrier' => 'instabox']
];

it('shows arrays correctly', function ($expectation, $options) {

    $result = DeliveryOptionsMerger::create($options);
    //expect($result)->toBeInstanceOf(AbstractDeliveryOptionsAdapter::class);
    expect($result->toArray())->toEqual($expectation);

})->with([
    [
        ['carrier' => 'postnl'],
        CARRIER[0]
    ],
    [
        ['carrier' => 'instabox'],
        CARRIER[1]
    ]
]);

//})->with([
//
//    [
//        [
//            'carrier'         => 'postnl',
//            'date'            => '2022-07-05T00:00:00.000Z',
//            'deliveryType'    => 'standard',
//            'packageType'     => 'package',
//            'isPickup'        => false,
//            'pickupLocation'  => null,
//            'shipmentOptions' => [
//                'signature'         => null,
//                'insurance'         => null,
//                'age_check'         => false,
//                'only_recipient'    => null,
//                'return'            => false,
//                'same_day_delivery' => null,
//                'large_format'      => null,
//                'label_description' => null,
//            ],
//        ],
//        DELIVERY_OPTIONS[0],
//    ],
//    [
//        [
//            'carrier'         => 'instabox',
//            'date'            => '2022-07-15T00:00:00.000Z',
//            'deliveryType'    => 'standard',
//            'packageType'     => 'mailbox',
//            'isPickup'        => false,
//            'pickupLocation'  => null,
//            'shipmentOptions' => [
//                'signature'         => true,
//                'insurance'         => 1000,
//                'age_check'         => true,
//                'only_recipient'    => true,
//                'return'            => false,
//                'same_day_delivery' => null,
//                'large_format'      => true,
//                'label_description' => 'ORDER_NMR',
//            ],
//        ],
//        DELIVERY_OPTIONS[1],
//    ],
//
//]);



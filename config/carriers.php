<?php
/** @noinspection DuplicatedCode */

declare(strict_types=1);

use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

return [
    [
        'id'                 => Carrier::CARRIER_POSTNL_ID,
        'name'               => Carrier::CARRIER_POSTNL_NAME,
        'primary'            => 1,
        'type'               => Carrier::TYPE_MAIN,
        'capabilities'       => [
            'packageTypes'    => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
            ],
            'deliveryTypes'   => [
                DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
            ],
            'shipmentOptions' => [
                'ageCheck'        => true,
                'largeFormat'     => true,
                'onlyRecipient'   => true,
                'return'          => true,
                'sameDayDelivery' => true,
                'signature'       => true,
                'insurance'       => [
                    0,
                    100,
                    250,
                    500,
                    1000,
                    1500,
                    2000,
                    2500,
                    3000,
                    3500,
                    4000,
                    4500,
                    5000,
                ],
            ],
            'features'        => [
                'labelDescriptionLength' => 45,
            ],
        ],
        'returnCapabilities' => [
            'packageTypes'    => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            ],
            'deliveryTypes'   => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            ],
            'shipmentOptions' => [
                'signature'       => true,
                'insurance'       => [
                    0,
                    100,
                    250,
                    500,
                    1000,
                    1500,
                    2000,
                    2500,
                    3000,
                    3500,
                    4000,
                    4500,
                    5000,
                ],
                'return'          => true,
                'ageCheck'        => true,
                'onlyRecipient'   => true,
                'sameDayDelivery' => true,
                'largeFormat'     => true,
            ],
            'features'        => [
                'labelDescriptionLength' => 45,
            ],
        ],
    ],
    [
        'id'                 => Carrier::CARRIER_INSTABOX_ID,
        'name'               => Carrier::CARRIER_INSTABOX_NAME,
        'primary'            => 1,
        'type'               => Carrier::TYPE_MAIN,
        'capabilities'       => [
            'packageTypes'           => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ],
            'deliveryTypes'          => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            ],
            'shipmentOptions'        => [
                'ageCheck'        => true,
                'largeFormat'     => true,
                'onlyRecipient'   => true,
                'return'          => true,
                'sameDayDelivery' => true,
                'signature'       => true,
            ],
            'labelDescriptionLength' => 45,
        ],
        'returnCapabilities' => [],
    ],
    [
        'id'                 => Carrier::CARRIER_BPOST_ID,
        'name'               => Carrier::CARRIER_BPOST_NAME,
        'subscriptionId'     => 10921,
        'primary'            => 0,
        'type'               => Carrier::TYPE_CUSTOM,
        'capabilities'       => [
            'packageTypes'    => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            ],
            'deliveryTypes'   => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            ],
            'shipmentOptions' => [
                'return'           => true,
                'saturdayDelivery' => true,
                'signature'        => true,
                'insurance'        => [
                    0,
                    500,
                ],
            ],
            'features'        => [
                'dropOffAtPostalPoint'   => true,
                'labelDescriptionLength' => 45,
            ],
        ],
        'returnCapabilities' => [
            'packageTypes'    => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            ],
            'deliveryTypes'   => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            ],
            'shipmentOptions' => [
                'largeFormat' => true,
                'signature'   => true,
                'insurance'   => [
                    0,
                    100,
                    250,
                    500,
                    1000,
                    1500,
                    2000,
                    2500,
                    3000,
                    3500,
                    4000,
                    4500,
                    5000,
                ],
            ],
            'features'        => [
                'labelDescriptionLength' => 45,
            ],
        ],
    ],
    [
        'id'                 => Carrier::CARRIER_DPD_ID,
        'name'               => Carrier::CARRIER_DPD_NAME,
        'subscriptionId'     => 10932621,
        'primary'            => 0,
        'type'               => Carrier::TYPE_CUSTOM,
        'capabilities'       => [
            'packageType'            => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            ],
            'deliveryTypes'          => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            ],
            'shipmentOptions'        => [
                'insurance' => [
                    520,
                ],
            ],
            'labelDescriptionLength' => 45,
        ],
        'returnCapabilities' => [],
    ],
    [
        'id'                 => Carrier::CARRIER_BPOST_ID,
        'name'               => Carrier::CARRIER_BPOST_NAME,
        'primary'            => 1,
        'type'               => Carrier::TYPE_MAIN,
        'capabilities'       => [
            'packageTypes'    => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            ],
            'deliveryTypes'   => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            ],
            'shipmentOptions' => [
                'saturdayDelivery' => true,
                'signature'        => true,
                'insurance'        => [
                    0,
                    500,
                ],
            ],
            'features'        => [
                'dropOffAtPostalPoint'   => true,
                'labelDescriptionLength' => 45,
                'multiCollo'             => true,
            ],
        ],
        'returnCapabilities' => [],
    ],
];

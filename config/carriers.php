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
                    10000,
                    25000,
                    50000,
                    100000,
                    150000,
                    200000,
                    250000,
                    300000,
                    350000,
                    400000,
                    450000,
                    500000,
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
                    10000,
                    25000,
                    50000,
                    100000,
                    150000,
                    200000,
                    250000,
                    300000,
                    350000,
                    400000,
                    450000,
                    500000,
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
        'id'                 => Carrier::CARRIER_DPD_ID,
        'name'               => Carrier::CARRIER_DPD_NAME,
        'subscriptionId'     => 10932623,
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
                    50000,
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
    [
        'id'           => Carrier::CARRIER_DHL_EUROPLUS_ID,
        'name'         => Carrier::CARRIER_DHL_EUROPLUS_NAME,
        'primary'      => 1,
        'type'         => Carrier::TYPE_MAIN,
        'capabilities' => [
            'packageTypes'    => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ],
            'deliveryTypes'   => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            ],
            'shipmentOptions' => [
                'signature' => true,
                'insurance' => [
                    0,
                    50000,
                ],
            ],
            'features'        => [
                'labelDescriptionLength' => 45,
            ],
        ],
    ],
    [
        'id'                 => Carrier::CARRIER_DHL_FOR_YOU_ID,
        'name'               => Carrier::CARRIER_DHL_FOR_YOU_NAME,
        'primary'            => 1,
        'type'               => Carrier::TYPE_MAIN,
        'capabilities'       => [
            'packageTypes'    => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
            ],
            'deliveryTypes'   => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
            ],
            'shipmentOptions' => [
                'ageCheck'         => true,
                'largeFormat'      => false,
                'onlyRecipient'    => true,
                'return'           => true,
                'sameDayDelivery'  => true,
                'signature'        => true,
                'saturdayDelivery' => true,
                'hideSender'       => true,
                'insurance'        => [
                    0,
                    50000,
                    100000,
                    150000,
                    200000,
                    250000,
                    300000,
                    350000,
                    400000,
                    450000,
                    500000,
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
        'id'           => Carrier::CARRIER_DHL_EUROPLUS_ID,
        'name'         => Carrier::CARRIER_DHL_EUROPLUS_NAME,
        'primary'      => 1,
        'type'         => Carrier::TYPE_MAIN,
        'capabilities' => [
            'packageTypes'    => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            ],
            'deliveryTypes'   => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            ],
            'shipmentOptions' => [
                'ageCheck'         => false,
                'onlyRecipient'    => false,
                'return'           => false,
                'sameDayDelivery'  => false,
                'signature'        => true,
                'saturdayDelivery' => true,
                'hideSender'       => true,
                'insurance'        => [
                    0,
                    50000,
                    100000,
                    150000,
                    200000,
                    250000,
                    300000,
                    350000,
                    400000,
                    450000,
                    500000,
                ],
            ],
            'features'        => [
                'labelDescriptionLength' => 45,
            ],
        ],
    ],
    [
        'id'                 => Carrier::CARRIER_DHL_PARCEL_CONNECT_ID,
        'name'               => Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
        'primary'            => 1,
        'type'               => Carrier::TYPE_MAIN,
        'capabilities'       => [
            'packageTypes'    => [
                DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
            ],
            'deliveryTypes'   => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
            ],
            'shipmentOptions' => [
                'ageCheck'         => false,
                'onlyRecipient'    => false,
                'return'           => false,
                'sameDayDelivery'  => false,
                'signature'        => true,
                'saturdayDelivery' => false,
                'hideSender'       => false,
                'insurance'        => [
                    0,
                    50000,
                    100000,
                    150000,
                    200000,
                    250000,
                    300000,
                    350000,
                    400000,
                    450000,
                    500000,
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
                'signature'       => false,
                'insurance'       => [
                    50000,
                ],
                'return'          => false,
                'ageCheck'        => false,
                'onlyRecipient'   => false,
                'sameDayDelivery' => false,
                'largeFormat'     => false,
            ],
            'features'        => [
                'labelDescriptionLength' => 45,
            ],
        ],
    ],
];

<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

return [
    'name'             => 'myparcel',
    'human'            => 'MyParcel',
    'backofficeUrl'    => 'https://backoffice.myparcel.nl',
    'supportUrl'       => 'https://developer.myparcel.nl/contact',
    'localCountry'     => CountryCodes::CC_NL,
    'defaultCarrier'   => Carrier::CARRIER_POSTNL_NAME,
    'defaultCarrierId' => Carrier::CARRIER_POSTNL_ID,

    'defaultSettings' => [
        CheckoutSettings::ID => [
            CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW => CheckoutSettings::PICKUP_LOCATIONS_VIEW_LIST,
        ],
    ],

    'carriers' => [
        [
            'name'               => Carrier::CARRIER_POSTNL_NAME,
            'capabilities'       => [
                'packageTypes'    => [
                    DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                    DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
                    DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME,
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
                    'sameDayDelivery' => false,
                    'signature'       => true,
                    'receiptCode'     => true,
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
                    'labelDescriptionLength'      => 45,
                    'carrierSmallPackageContract' => CarrierSchema::FEATURE_CUSTOM_CONTRACT_ONLY,
                    'multiCollo'                  => true,
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
                    'sameDayDelivery' => false,
                    'largeFormat'     => true,
                ],
                'features'        => [
                    'labelDescriptionLength' => 45,
                ],
            ],
        ],

        [
            'name'               => Carrier::CARRIER_DHL_FOR_YOU_NAME,
            'capabilities'       => [
                'packageTypes'    => [
                    DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                    DeliveryOptions::PACKAGE_TYPE_PACKAGE_SMALL_NAME
                ],
                'deliveryTypes'   => [
                    DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                    DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                ],
                'shipmentOptions' => [
                    'ageCheck'         => true,
                    'largeFormat'      => false,
                    'onlyRecipient'    => true,
                    'return'           => false,
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
            'name'               => Carrier::CARRIER_DHL_PARCEL_CONNECT_NAME,
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

        [
            'name'         => Carrier::CARRIER_DHL_EUROPLUS_NAME,
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
            'name'         => Carrier::CARRIER_UPS_NAME,
            'capabilities' => [
                'packageTypes'    => [
                    DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                ],
                'deliveryTypes'   => [
                    DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                ],
                'shipmentOptions' => [
                    'ageCheck'      => false,
                    'onlyRecipient' => false,
                    'return'        => false,
                ],
                'features'        => [
                    'labelDescriptionLength' => 45,
                ],
            ],
        ],
        [
            'name'         => Carrier::CARRIER_DPD_NAME,
            'capabilities' => [
                'packageTypes'    => [
                    DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                    DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                ],
                'deliveryTypes'   => [
                    DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                    DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                ],
                'shipmentOptions' => [
                    'ageCheck'      => false,
                    'onlyRecipient' => false,
                    'return'        => false,
                ],
                'features'        => [
                    'labelDescriptionLength' => 45,
                    'needsCustomerInfo'      => true,
                ],
            ],
        ],
    ],
];


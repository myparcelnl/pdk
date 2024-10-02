<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

return [
    'name'             => 'belgie',
    'human'            => 'SendMyParcel',
    'backofficeUrl'    => 'https://backoffice.sendmyparcel.be',
    'supportUrl'       => 'https://developer.myparcel.nl/contact',
    'localCountry'     => CountryCodes::CC_BE,
    'defaultCarrier'   => Carrier::CARRIER_BPOST_NAME,
    'defaultCarrierId' => Carrier::CARRIER_BPOST_ID,

    'defaultSettings' => [
        CheckoutSettings::ID => [
            CheckoutSettings::PICKUP_LOCATIONS_DEFAULT_VIEW => CheckoutSettings::PICKUP_LOCATIONS_VIEW_MAP,
        ],
    ],

    'carriers' => [
        [
            'name'               => Carrier::CARRIER_POSTNL_NAME,
            'capabilities'       => [
                'packageTypes'    => [
                    DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                ],
                'deliveryTypes'   => [
                    DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                    DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                ],
                'shipmentOptions' => [
                    'signature'     => true,
                    'ageCheck'      => false,
                    'return'        => false,
                    'onlyRecipient' => true,
                    'largeFormat'   => true,
                    'insurance'     => [
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
                    'multiCollo'             => true,
                ],
            ],
            'returnCapabilities' => [],
        ],
        [
            'name'         => Carrier::CARRIER_BPOST_NAME,
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
                    'saturdayDelivery' => true,
                    'signature'        => true,
                    'insurance'        => [
                        0,
                        50000,
                        250000,
                        500000,
                    ],
                ],
                'features'        => [
                    'labelDescriptionLength' => 45,
                    'multiCollo'             => true,
                ],
            ],
        ],
        [
            'name'               => Carrier::CARRIER_DPD_NAME,
            'capabilities'       => [
                'packageTypes'    => [
                    DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                ],
                'deliveryTypes'   => [
                    DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                    DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                ],
                'shipmentOptions' => [
                    'insurance' => [52000],
                ],
                'features'        => [
                    'dropOffAtPostalPoint'   => true,
                    'labelDescriptionLength' => 45,
                    'multiCollo'             => true,
                    'needsCustomerInfo'      => true,
                ],
            ],
            'returnCapabilities' => [],
        ],
    ],
];

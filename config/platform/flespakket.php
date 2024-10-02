<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

return [
    'name'             => 'flespakket',
    'human'            => 'Flespakket',
    'backofficeUrl'    => 'https://backoffice.flespakket.nl',
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
    ],
];

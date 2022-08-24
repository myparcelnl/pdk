<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

return [
    'platform' => [
        [
            'name'    => 'myparcel',
            'human'   => 'MyParcel',
            'base_cc' => CountryCodes::CC_NL,
            'carrier' => [
                [
                    'id'     => CarrierOptions::CARRIER_POSTNL_ID,
                    'name'   => CarrierOptions::CARRIER_POSTNL_NAME,
                    'human'  => 'PostNL',

                    // CORRECT
                    'schema' => [
                        'type'                 => 'object',
                        'additionalProperties' => true,
                        'properties'           => [
                            'deliveryOptions' => [
                                'type'                 => 'object',
                                'additionalProperties' => true,
                                'properties'           => [
                                    'shipmentOptions' => [
                                        'type'                 => 'object',
                                        'additionalProperties' => true,
                                        'properties'           => [
                                            'labelDescription' => [
                                                'type'    => 'string',
                                                'minimum' => 0,
                                                'maximum' => 45,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'shippingZone' => [
                        [
                            'cc'          => CountryCodes::CC_NL,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,

                                    // CORRECT
                                    'schema'       => [
                                        'type'                 => 'object',
                                        'additionalProperties' => true,
                                        'properties'           => [
                                            'weight'          => [
                                                'minimum' => 0,
                                                'maximum' => 23000,
                                            ],
                                            'deliveryOptions' => [
                                                'type'                 => 'object',
                                                'additionalProperties' => true,
                                                'properties'           => [
                                                    'shipmentOptions' => [
                                                        'type'                 => 'object',
                                                        'additionalProperties' => true,
                                                        'properties'           => [
                                                            'insurance'     => [
                                                                'type' => 'integer',
                                                                'enum' => [
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
                                                            'ageCheck'      => [
                                                                'type' => 'boolean',
                                                                'enum' => [false, true],
                                                            ],
                                                            'signature'     => [
                                                                'type' => 'boolean',
                                                                'enum' => [false, true],
                                                            ],
                                                            'onlyRecipient' => [
                                                                'type' => 'boolean',
                                                                'enum' => [false, true],
                                                            ],
                                                            'return'        => [
                                                                'type' => 'boolean',
                                                                'enum' => [false, true],
                                                            ],
                                                            'largeFormat'   => [
                                                                'type' => 'boolean',
                                                                'enum' => [false, true],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                        [
                                            'id'     => DeliveryOptions::DELIVERY_TYPE_MORNING_ID,
                                            'name'   => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                                            // CORRECT
                                            'schema' => [
                                                'type'                 => 'object',
                                                'additionalProperties' => true,
                                                'properties'           => [
                                                    'deliveryOptions' => [
                                                        'type'                 => 'object',
                                                        'additionalProperties' => true,
                                                        'properties'           => [
                                                            'shipmentOptions' => [
                                                                'type'                 => 'object',
                                                                'additionalProperties' => true,
                                                                'properties'           => [
                                                                    'ageCheck'      => [
                                                                        'type' => 'boolean',
                                                                        'enum' => [false],
                                                                    ],
                                                                    'onlyRecipient' => [
                                                                        'type' => 'boolean',
                                                                        'enum' => [true],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_EVENING_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,

                                            'schema' => [
                                                'type'                 => 'object',
                                                'additionalProperties' => true,
                                                'properties'           => [
                                                    'deliveryOptions' => [
                                                        'type'                 => 'object',
                                                        'additionalProperties' => true,
                                                        'properties'           => [
                                                            'shipmentOptions' => [
                                                                'type'                 => 'object',
                                                                'additionalProperties' => true,
                                                                'properties'           => [
                                                                    'ageCheck'      => [
                                                                        'type' => 'boolean',
                                                                        'enum' => [false],
                                                                    ],
                                                                    'onlyRecipient' => [
                                                                        'type' => 'boolean',
                                                                        'enum' => [true],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                        [
                                            'id'     => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                            'schema' => [
                                                'type'                 => 'object',
                                                'additionalProperties' => true,
                                                'properties'           => [
                                                    'deliveryOptions' => [
                                                        'type'                 => 'object',
                                                        'additionalProperties' => true,
                                                        'properties'           => [
                                                            'pickupLocation'  => [
                                                                'type'                 => 'object',
                                                                'required'             => ['locationCode'],
                                                                'additionalProperties' => true,
                                                                'properties'           => [
                                                                    'locationCode' => [
                                                                        'type' => 'string',
                                                                        'enum' => [true],
                                                                    ],
                                                                ],
                                                            ],
                                                            'shipmentOptions' => [
                                                                'type'                 => 'object',
                                                                'additionalProperties' => true,
                                                                'properties'           => [
                                                                    'signature'     => [
                                                                        'type' => 'boolean',
                                                                        'enum' => [true],
                                                                    ],
                                                                    'onlyRecipient' => [
                                                                        'type' => 'boolean',
                                                                        'enum' => [false],
                                                                    ],
                                                                    'return'        => [
                                                                        'type' => 'boolean',
                                                                        'enum' => [false],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::CC_BE,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'largeFormat' => [
                                            'type' => 'boolean',
                                            'enum' => [false, true],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight'      => [
                                            'minimum' => 1,
                                            'maximum' => 23000,
                                        ],
                                        'largeFormat' => [
                                            'enum'     => [true],
                                            'property' => 'weight',
                                            'minimum'  => 0,
                                            'maximum'  => 30000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                            'options' => [
                                                'insurance'     => [
                                                    'type' => 'integer',
                                                    'enum' => [
                                                        0,
                                                        500,
                                                    ],
                                                ],
                                                'signature'     => [
                                                    'type' => 'boolean',
                                                    'enum' => [true],
                                                ],
                                                'onlyRecipient' => [
                                                    'type' => 'boolean',
                                                    'enum' => [true],
                                                ],
                                            ],
                                        ],
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::ZONE_EU,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance'   => [
                                            'type' => 'integer',
                                            'enum' => [500,],
                                        ],
                                        'largeFormat' => [
                                            'type' => 'boolean',
                                            'enum' => [true, false],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight'      => [
                                            'minimum' => 1,
                                            'maximum' => 23000,
                                        ],
                                        'largeFormat' => [
                                            'enum'     => [true],
                                            'property' => 'weight',
                                            'minimum'  => 0,
                                            'maximum'  => 30000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::ZONE_ROW,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance' => [
                                            'type' => 'integer',
                                            'enum' => [
                                                200,
                                            ],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 20000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id'           => CarrierOptions::CARRIER_INSTABOX_ID,
                    'name'         => CarrierOptions::CARRIER_INSTABOX_NAME,
                    'human'        => 'Instabox',
                    'requirements' => [
                        'labelDescription' => [
                            'type'    => 'string',
                            'minimum' => 0,
                            'maximum' => 45,
                        ],
                    ],
                    'shippingZone' => [
                        [
                            'cc'          => CountryCodes::CC_NL,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance'       => [
                                            'type' => 'integer',
                                            'enum' => [
                                                0,
                                            ],
                                        ],
                                        'sameDayDelivery' => [
                                            'type' => 'boolean',
                                            'enum' => [false, true],
                                        ],
                                        'ageCheck'        => [
                                            'type' => 'boolean',
                                            'enum' => [false, true],
                                        ],
                                        'signature'       => [
                                            'type' => 'boolean',
                                            'enum' => [true],
                                        ],
                                        'onlyRecipient'   => [
                                            'type' => 'boolean',
                                            'enum' => [false, true],
                                        ],
                                        'return'          => [
                                            'type' => 'boolean',
                                            'enum' => [false, true],
                                        ],
                                        'largeFormat'     => [
                                            'type' => 'boolean',
                                            'enum' => [false, true],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight'      => [
                                            'minimum' => 0,
                                            'maximum' => 30000,
                                        ],
                                        'ageCheck'    => [
                                            'enum'    => [true],
                                            'options' => [
                                                'signature'     => [true],
                                                'onlyRecipient' => [true],
                                            ],
                                        ],
                                        'largeFormat' => [
                                            'enum'     => [true],
                                            'property' => 'weight',
                                            'minimum'  => 20000,
                                            'maximum'  => 30000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                                    'options'      => [
                                        'sameDayDelivery' => [
                                            'type' => 'boolean',
                                            'enum' => [false, true],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        [
            'name'     => 'belgie',
            'human'    => 'Sendmyparcel',
            'base_cc'  => CountryCodes::CC_BE,
            'carriers' => [
                [
                    'id'           => CarrierOptions::CARRIER_POSTNL_ID,
                    'name'         => CarrierOptions::CARRIER_POSTNL_ID,
                    'human'        => 'PostNL',
                    'requirements' => [
                        'labelDescription' => [
                            'type'    => 'string',
                            'minimum' => 0,
                            'maximum' => 45,
                        ],
                    ],
                    'shippingZone' => [
                        [
                            'cc'          => CountryCodes::CC_BE,
                            'schema'      => [],
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'largeFormat'   => [
                                            'type' => 'boolean',
                                            'enum' => [true, false],
                                        ],
                                        'signature'     => [
                                            'type' => 'boolean',
                                            'enum' => [true, false],
                                        ],
                                        'onlyRecipient' => [
                                            'type' => 'boolean',
                                            'enum' => [true, false],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight'      => [
                                            'minimum' => 1,
                                            'maximum' => 23000,
                                        ],
                                        'largeFormat' => [
                                            'enum'     => [true],
                                            'property' => 'weight',
                                            'minimum'  => 0,
                                            'maximum'  => 30000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                            'options' => [
                                                'insurance'     => [
                                                    'type' => 'integer',
                                                    'enum' => [
                                                        0,
                                                        500,
                                                    ],
                                                ],
                                                'signature'     => [
                                                    'type' => 'boolean',
                                                    'enum' => [false, true],
                                                ],
                                                'onlyRecipient' => [
                                                    'type' => 'boolean',
                                                    'enum' => [false, true],
                                                ],
                                            ],
                                        ],
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::CC_NL,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance'   => [
                                            'type' => 'integer',
                                            'enum' => [
                                                0,
                                                500,
                                            ],
                                        ],
                                        'largeFormat' => [
                                            'type' => 'boolean',
                                            'enum' => [true, false],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight'      => [
                                            'minimum' => 0,
                                            'maximum' => 22999,
                                        ],
                                        'largeFormat' => [
                                            'enum'     => [true],
                                            'property' => 'weight',
                                            'minimum'  => 23000,
                                            'maximum'  => 30000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::ZONE_EU,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance'   => [
                                            'type' => 'integer',
                                            'enum' => [
                                                500,
                                            ],
                                        ],
                                        'largeFormat' => [
                                            'type' => 'boolean',
                                            'enum' => [true, false],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight'      => [
                                            'minimum' => 1,
                                            'maximum' => 23000,
                                        ],
                                        'largeFormat' => [
                                            'enum'     => [true],
                                            'property' => 'weight',
                                            'minimum'  => 0,
                                            'maximum'  => 30000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'           => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name'         => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                            'requirements' => [
                                                'weight'      => [
                                                    'minimum' => 1,
                                                    'maximum' => 23000,
                                                ],
                                                'largeFormat' => [
                                                    'enum'     => [true],
                                                    'property' => 'weight',
                                                    'minimum'  => 0,
                                                    'maximum'  => 30000,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::ZONE_ROW,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance' => [
                                            'type' => 'integer',
                                            'enum' => [
                                                200,
                                            ],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 20000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id'           => CarrierOptions::CARRIER_BPOST_ID,
                    'name'         => CarrierOptions::CARRIER_BPOST_NAME,
                    'human'        => 'Bpost',
                    'requirements' => [
                        'labelDescription' => [
                            'type'    => 'string',
                            'minimum' => 0,
                            'maximum' => 45,
                        ],
                    ],
                    'shippingZone' => [
                        [
                            'cc'          => CountryCodes::CC_BE,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'signature' => [
                                            'type' => 'boolean',
                                            'enum' => [false, true],
                                        ],
                                        'insurance' => [
                                            'type' => 'boolean',
                                            'enum' => [
                                                0,
                                                500,
                                            ],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 30000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::ZONE_EU,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance' => [
                                            'type' => 'integer',
                                            'enum' => [
                                                0,
                                                500,
                                            ],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 30000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::ZONE_ROW,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::ZONE_ROW,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance' => [
                                            'type' => 'integer',
                                            'enum' => [
                                                0,
                                                500,
                                            ],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 20000,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'id'           => 4,
                    'name'         => 'dpd',
                    'human'        => 'DPD',
                    'requirements' => [
                        'labelDescription' => [
                            'type'    => 'string',
                            'minimum' => 0,
                            'maximum' => 45,
                        ],
                    ],
                    'shippingZone' => [
                        [
                            'cc'          => CountryCodes::CC_BE,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance' => [
                                            'type' => 'integer',
                                            'enum' => [
                                                520,
                                            ],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 31500,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::CC_NL,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance' => [
                                            'type' => 'integer',
                                            'enum' => [
                                                520,
                                            ],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 31500,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'          => CountryCodes::ZONE_EU,
                            'packageType' => [
                                [
                                    'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'      => [
                                        'insurance' => [
                                            'type' => 'integer',
                                            'enum' => [
                                                520,
                                            ],
                                        ],
                                    ],
                                    'requirements' => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 31500,
                                        ],
                                    ],
                                    'deliveryType' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];

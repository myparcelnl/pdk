<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation;

use MyParcelNL\Pdk\Base\ConfigInterface;
use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\src\Support\Arr;

class ValidationSchema implements ConfigInterface
{

    public const VALIDATION_SCHEMA_TWO = [
        'platform' => [
            'name'     => 'myparcel',
            'human'    => 'MyParcel',
            'base_cc'  => CountryCodes::CC_NL,
            'carriers' => [
                [
                    'id'            => 1,
                    'name'          => 'postnl',
                    'human'         => 'PostNL',
                    'options'       => [
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                    'shippingZones' => [
                        [
                            'cc'           => CountryCodes::CC_NL,
                            'packageTypes' => [
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'       => [
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
                                            'type'         => 'boolean',
                                            'enum'         => [false, true],
                                            'requirements' => [
                                                'signature'     => ['enum' => true],
                                                'onlyRecipient' => ['enum' => true],
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
                                        'return'        => [
                                            'type' => 'boolean',
                                            'enum' => [false, true],
                                        ],
                                        'largeFormat'   => [
                                            'type'         => 'boolean',
                                            'enum'         => [false, true],
                                            'requirements' => [
                                                'weight' => [
                                                    'minimum' => 0,
                                                    'maximum' => 30000,
                                                ],
                                            ],
                                        ],
                                    ],
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 23000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_MORNING_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                                            'options' => [
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
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_EVENING_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                                            'options' => [
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
                                        [
                                            'id'           => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name'         => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                            'options'      => [
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
                                            'requirements' => [
                                                'pickupLocation' => [
                                                    'locationCode' => [
                                                        'type' => 'string',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'           => CountryCodes::CC_BE,
                            'packageTypes' => [
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'       => [
                                        'largeFormat' => [
                                            'values'       => [0, 1],
                                            'requirements' => [
                                                'weight' => [
                                                    'minimum' => 0,
                                                    'maximum' => 30000,
                                                ],
                                            ],
                                        ],
                                    ],
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 23000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
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
                                                'signature'     => ['type' => 'boolean', 'enum' => true,],
                                                'onlyRecipient' => ['type' => 'boolean', 'enum' => true,],
                                            ],
                                        ],
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'           => CountryCodes::ZONE_EU,
                            'packageTypes' => [
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'       => [
                                        'insurance'   => [
                                            'type' => 'integer',
                                            'enum' => [500,],
                                        ],
                                        'largeFormat' => [
                                            'type'         => 'boolean',
                                            'enum'         => [true, false],
                                            'requirements' => [
                                                'weight' => [
                                                    'minimum' => 1,
                                                    'maximum' => 30000,
                                                ],
                                            ],
                                        ],
                                    ],
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 23000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'           => CountryCodes::ZONE_ROW,
                            'packageTypes' => [
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 20000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                            'options' => [
                                                'insurance' => [
                                                    'type' => 'integer',
                                                    'enum' => [
                                                        200,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
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
    ];
    public const VALIDATION_SCHEMA = [
        'platform' => [
            'name'     => 'myparcel',
            'human'    => 'MyParcel',
            'base_cc'  => CountryCodes::CC_NL,
            'carriers' => [
                [
                    'id'            => 1,
                    'name'          => 'postnl',
                    'human'         => 'PostNL',
                    'options'       => [
                        'labelDescription' => [
                            'values'       => [0, 1],
                            'requirements' => [
                                'minLength' => 0,
                                'maxLength' => 45,
                            ],
                        ],
                    ],
                    'shippingZones' => [
                        [
                            'cc'           => CountryCodes::CC_NL,
                            'packageTypes' => [
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'       => [
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
                                        'ageCheck'        => [
                                            'requirements' => [
                                                'signature'     => [1],
                                                'onlyRecipient' => [1],
                                            ],
                                            'values'       => [0, 1],
                                        ],
                                        'signature'       => [0, 1],
                                        'onlyRecipient'   => [0, 1],
                                        'return'          => [0, 1],
                                        'sameDayDelivery' => [0],
                                        'largeFormat'     => [
                                            'values'       => [0, 1],
                                            'requirements' => [
                                                'weight' => [
                                                    'minimum' => 0,
                                                    'maximum' => 30000,
                                                ],
                                            ],
                                        ],
                                    ],
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 23000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_MORNING_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                                            'options' => [
                                                'ageCheck'      => [0],
                                                'onlyRecipient' => [1],
                                            ],
                                        ],
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_EVENING_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                                            'options' => [
                                                'ageCheck'      => [0],
                                                'onlyRecipient' => [1],
                                            ],
                                        ],
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                            'options' => [
                                                'signature'     => [1],
                                                'onlyRecipient' => [0],
                                                'return'        => [0],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'           => CountryCodes::CC_BE,
                            'packageTypes' => [
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'       => [
                                        'largeFormat' => [
                                            'values'       => [0, 1],
                                            'requirements' => [
                                                'weight' => [
                                                    'minimum' => 0,
                                                    'maximum' => 30000,
                                                ],
                                            ],
                                        ],
                                    ],
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 23000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                            'options' => [
                                                'insurance'     => [
                                                    0,
                                                    500,
                                                ],
                                                'signature'     => [1],
                                                'onlyRecipient' => [1],
                                            ],
                                        ],
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'           => 'EU',
                            'packageTypes' => [
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'options'       => [
                                        'insurance'   => [
                                            500,
                                        ],
                                        'largeFormat' => [
                                            'values'       => [0, 1,],
                                            'requirements' => [
                                                'weight' => [
                                                    'minimum' => 1,
                                                    'maximum' => 30000,
                                                ],
                                            ],
                                        ],
                                    ],
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 23000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'deliveryTypes' => [
                                        [
                                            'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'cc'           => 'ROW',
                            'packageTypes' => [
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 20000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                            'options' => [
                                                'insurance' => [
                                                    200,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
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
                    'id'            => 5,
                    'name'          => 'instabox',
                    'human'         => 'Instabox',
                    'options'       => [
                        'labelDescription' => [
                            'values'       => [0, 1,],
                            'requirements' => [
                                'minLength' => 0,
                                'maxLength' => 45,
                            ],
                        ],

                    ],
                    'shippingZones' => [
                        [
                            'cc'           => CountryCodes::CC_NL,
                            'options' => [
                                'sameDayDelivery'  => [0, 1],
                            ],
                            'packageTypes' => [
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 0,
                                            'maximum' => 20000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                            'options' => [
                                                'ageCheck'         => [0, 1],
                                                'onlyRecipient'    => [0, 1],
                                                'return'           => [0, 1],
                                                'largeFormat'      => [
                                                    'values'       => [0, 1],
                                                    'requirements' => [
                                                        'weight' => [
                                                            'minimum' => 1,
                                                            'maximum' => 30000,
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'id'            => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
                                    'name'          => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                                    'requirements'  => [
                                        'weight' => [
                                            'minimum' => 1,
                                            'maximum' => 2000,
                                        ],
                                    ],
                                    'deliveryTypes' => [
                                        [
                                            'id'      => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                            'name'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
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
    public const EU_COUNTRIES      = [
        'NL',
        'BE',
        'AT',
        'BG',
        'CZ',
        'CY',
        'DK',
        'EE',
        'FI',
        'FR',
        'DE',
        'GR',
        'HU',
        'IE',
        'IT',
        'LV',
        'LT',
        'LU',
        'PL',
        'PT',
        'RO',
        'SK',
        'SI',
        'ES',
        'SE',
        'XK',
    ];

    /**
     * @param  string $key
     *
     * @return array|\ArrayAccess|mixed
     */
    public function get(string $key)
    {
        return Arr::get(self::VALIDATION_SCHEMA, $key);
    }
}

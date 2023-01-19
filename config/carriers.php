<?php
/** @noinspection DuplicatedCode */

declare(strict_types=1);

use MyParcelNL\Pdk\Carrier\Model\Carrier;

return [
    [
        'id'                 => Carrier::CARRIER_POSTNL_ID,
        'name'               => Carrier::CARRIER_POSTNL_NAME,
        'primary'            => 1,
        'type'               => Carrier::TYPE_MAIN,
        'capabilities'       => [
            [
                'packageType'     => [
                    'id'   => 1,
                    'name' => 'package',
                ],
                'deliveryTypes'   => [
                    [
                        'id'   => 1,
                        'name' => 'morning',
                    ],
                    [
                        'id'   => 2,
                        'name' => 'standard',
                    ],
                    [
                        'id'   => 3,
                        'name' => 'evening',
                    ],
                    [
                        'id'   => 4,
                        'name' => 'pickup',
                    ],
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean'],
                    'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                    'largeFormat'          => ['type' => 'boolean'],
                    'onlyRecipient'        => ['type' => 'boolean'],
                    'return'               => ['type' => 'boolean'],
                    'sameDayDelivery'      => ['type' => 'boolean'],
                    'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                    'signature'            => ['type' => 'boolean'],
                    'insurance'            => [
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
                    'labelDescription'     => [
                        'type'      => 'string',
                        'minLength' => 0,
                        'maxLength' => 45,
                    ],
                ],
            ],
            [
                'packageType'     => [
                    'id'   => 2,
                    'name' => 'mailbox',
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                    'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                    'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                    'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                    'return'               => ['type' => 'boolean', 'enum' => [false]],
                    'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                    'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                    'signature'            => ['type' => 'boolean', 'enum' => [false]],
                    'labelDescription'     => [
                        'type'      => 'string',
                        'minLength' => 0,
                        'maxLength' => 45,
                    ],
                ],
            ],
            [
                'packageType'     => [
                    'id'   => 3,
                    'name' => 'letter',
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                    'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                    'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                    'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                    'return'               => ['type' => 'boolean', 'enum' => [false]],
                    'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                    'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                    'signature'            => ['type' => 'boolean', 'enum' => [false]],
                    'labelDescription'     => ['type' => 'null'],
                ],
            ],
            [
                'packageType'     => [
                    'id'   => 4,
                    'name' => 'digital_stamp',
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                    'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                    'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                    'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                    'return'               => ['type' => 'boolean', 'enum' => [false]],
                    'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                    'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                    'signature'            => ['type' => 'boolean', 'enum' => [false]],
                    'labelDescription'     => ['type' => 'null'],
                ],
            ],
        ],
        'returnCapabilities' => [
            [
                'packageType'     => [
                    'id'   => 1,
                    'name' => 'package',
                ],
                'deliveryTypes'   => [
                    [
                        'id'   => 2,
                        'name' => 'standard',
                    ],
                ],
                'shipmentOptions' => [
                    'signature'        => ['type' => 'boolean'],
                    'insurance'        => [
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
                    'return'           => ['type' => 'boolean'],
                    'ageCheck'         => ['type' => 'boolean'],
                    'onlyRecipient'    => ['type' => 'boolean'],
                    'sameDayDelivery'  => ['type' => 'boolean'],
                    'largeFormat'      => ['type' => 'boolean'],
                    'labelDescription' => [
                        'type'      => 'string',
                        'minLength' => 0,
                        'maxLength' => 45,
                    ],
                ],
            ],
            [
                'packageType'     => [
                    'id'   => 2,
                    'name' => 'mailbox',
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                    'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                    'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                    'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                    'return'               => ['type' => 'boolean', 'enum' => [false]],
                    'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                    'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                    'signature'            => ['type' => 'boolean', 'enum' => [false]],
                    'insurance'            => ['type' => 'null'],
                    'labelDescription'     => [
                        'type'      => 'string',
                        'minLength' => 0,
                        'maxLength' => 45,
                    ],
                ],
            ],
        ],
    ],
    [
        'id'                 => Carrier::CARRIER_INSTABOX_ID,
        'name'               => Carrier::CARRIER_INSTABOX_NAME,
        'primary'            => 1,
        'type'               => Carrier::TYPE_MAIN,
        'capabilities'       => [
            [
                'packageType'     => [
                    'id'   => 1,
                    'name' => 'package',
                ],
                'deliveryTypes'   => [
                    [
                        'id'   => 2,
                        'name' => 'standard',
                    ],
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean'],
                    'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                    'largeFormat'          => ['type' => 'boolean'],
                    'onlyRecipient'        => ['type' => 'boolean'],
                    'return'               => ['type' => 'boolean'],
                    'sameDayDelivery'      => ['type' => 'boolean'],
                    'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                    'signature'            => ['type' => 'boolean'],
                    'insurance'            => ['type' => 'null'],
                    'labelDescription'     => [
                        'type'      => 'string',
                        'minLength' => 0,
                        'maxLength' => 45,
                    ],
                ],
            ],
            [
                'packageType'     => [
                    'id'   => 2,
                    'name' => 'mailbox',
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                    'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                    'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                    'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                    'return'               => ['type' => 'boolean', 'enum' => [false]],
                    'sameDayDelivery'      => ['type' => 'boolean'],
                    'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                    'signature'            => ['type' => 'boolean'],
                    'insurance'            => ['type' => 'null'],
                    'labelDescription'     => [
                        'type'      => 'string',
                        'minLength' => 0,
                        'maxLength' => 45,
                    ],
                ],
            ],
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
            [
                'packageType'     => [
                    'id'   => 1,
                    'name' => 'package',
                ],
                'deliveryTypes'   => [
                    [
                        'id'   => 2,
                        'name' => 'standard',
                    ],
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                    'dropOffAtPostalPoint' => ['type' => 'boolean'],
                    'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                    'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                    'return'               => ['type' => 'boolean'],
                    'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                    'saturdayDelivery'     => ['type' => 'boolean'],
                    'signature'            => ['type' => 'boolean'],
                    'insurance'            => [
                        'type' => 'integer',
                        'enum' => [
                            0,
                            500,
                        ],
                    ],
                    'labelDescription'     => [
                        'type'      => 'string',
                        'minLength' => 0,
                        'maxLength' => 45,
                    ],
                ],
            ],
        ],
        'returnCapabilities' => [
            [
                'packageType'     => [
                    'id'   => 1,
                    'name' => 'package',
                ],
                'deliveryTypes'   => [
                    [
                        'id'   => 2,
                        'name' => 'standard',
                    ],
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                    'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                    'largeFormat'          => ['type' => 'boolean'],
                    'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                    'return'               => ['type' => 'boolean', 'enum' => [false]],
                    'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                    'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                    'signature'            => ['type' => 'boolean'],
                    'insurance'            => [
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
                    'labelDescription'     => [
                        'type'      => 'string',
                        'minLength' => 0,
                        'maxLength' => 45,
                    ],
                ],
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
            [
                'packageType'     => [
                    'id'   => 1,
                    'name' => 'package',
                ],
                'deliveryTypes'   => [
                    [
                        'id'   => 2,
                        'name' => 'standard',
                    ],
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                    'dropOffAtPostalPoint' => ['type' => 'boolean', 'enum' => [false]],
                    'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                    'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                    'return'               => ['type' => 'boolean', 'enum' => [false]],
                    'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                    'saturdayDelivery'     => ['type' => 'boolean', 'enum' => [false]],
                    'signature'            => ['type' => 'boolean', 'enum' => [false]],
                    'insurance'            => [
                        'type' => 'integer',
                        'enum' => [
                            520,
                        ],
                    ],
                    'labelDescription'     => [
                        'type'      => 'string',
                        'minLength' => 0,
                        'maxLength' => 45,
                    ],
                ],
            ],
        ],
        'returnCapabilities' => [],
    ],
    [
        'id'                 => Carrier::CARRIER_BPOST_ID,
        'name'               => Carrier::CARRIER_BPOST_NAME,
        'primary'            => 1,
        'type'               => Carrier::TYPE_MAIN,
        'capabilities'       => [
            [
                'packageType'     => [
                    'id'   => 1,
                    'name' => 'package',
                ],
                'deliveryTypes'   => [
                    [
                        'id'   => 2,
                        'name' => 'standard',
                    ],
                ],
                'shipmentOptions' => [
                    'ageCheck'             => ['type' => 'boolean', 'enum' => [false]],
                    'dropOffAtPostalPoint' => ['type' => 'boolean'],
                    'largeFormat'          => ['type' => 'boolean', 'enum' => [false]],
                    'onlyRecipient'        => ['type' => 'boolean', 'enum' => [false]],
                    'return'               => ['type' => 'boolean', 'enum' => [false]],
                    'sameDayDelivery'      => ['type' => 'boolean', 'enum' => [false]],
                    'saturdayDelivery'     => ['type' => 'boolean'],
                    'signature'            => ['type' => 'boolean'],
                    'insurance'            => [
                        'type' => 'integer',
                        'enum' => [
                            0,
                            500,
                        ],
                    ],
                    'labelDescription'     => [
                        'type'      => 'string',
                        'minLength' => 0,
                        'maxLength' => 45,
                    ],
                ],
            ],
        ],
        'returnCapabilities' => [],
    ],
];

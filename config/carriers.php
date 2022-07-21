<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Shipment\Model\Options\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Validator\BpostShipmentValidator;
use MyParcelNL\Pdk\Shipment\Validator\DPDShipmentValidator;
use MyParcelNL\Pdk\Shipment\Validator\InstaboxShipmentValidator;
use MyParcelNL\Pdk\Shipment\Validator\PostNLShipmentValidator;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierBpost;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierDPD;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierInstabox;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierPostNL;
use MyParcelNL\Sdk\src\Model\Consignment\AbstractConsignment;

// Correct version after meeting with API
$correctVersion = [
    'carriers' => [
        [
            'id'               => 1,
            'name'             => 'postnl',
            'primary'          => 1,
            'type'             => 'main',
            'recipientOptions' => [
                'packageTypes' => [
                    [
                        'id'           => 1,
                        'name'         => 'package',
                        'deliveryType' => [
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
                        'options'      => [
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
                            'ageCheck'         => ['type' => 'boolean'],
                            'onlyRecipient'    => ['type' => 'boolean'],
                            'return'           => ['type' => 'boolean'],
                            'sameDayDelivery'  => ['type' => 'boolean'],
                            'largeFormat'      => ['type' => 'boolean'],
                            'labelDescription' => [
                                'type'          => 'string',
                                'minimumLength' => 0,
                                'maximumLength' => 45,
                            ],
                        ],
                        'requirements' => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 30000,
                            ],
                        ],
                    ],
                    [
                        'id'           => 2,
                        'name'         => 'mailbox',
                        'options'      => [
                            'labelDescription' => [
                                'type'          => 'string',
                                'minimumLength' => 0,
                                'maximumLength' => 45,
                            ],
                        ],
                        'requirements' => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 2000,
                            ],
                        ],
                    ],
                    [
                        'id'           => 3,
                        'name'         => 'letter',
                        'options'      => [],
                        'requirements' => [],
                    ],
                    [
                        'id'           => 4,
                        'name'         => 'digital_stamp',
                        'options'      => [
                            'weightClasses' => [
                                [0, 20],
                                [20, 50],
                                [50, 100],
                                [100, 350],
                                [350, 2000],
                            ],
                        ],
                        'requirements' => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 2000,
                            ],
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [
                [
                    'packageTypes' => [
                        [
                            'id'            => 1,
                            'name'          => 'package',
                            'deliveryTypes' => [
                                [
                                    'id'   => 2,
                                    'name' => 'standard',
                                ],
                            ],
                            'options'       => [
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
                                    'type'          => 'string',
                                    'minimumLength' => 0,
                                    'maximumLength' => 45,
                                ],
                            ],
                        ],
                        [
                            'id'      => 2,
                            'name'    => 'mailbox',
                            'options' => [
                                'labelDescription' => [
                                    'type'          => 'string',
                                    'minimumLength' => 0,
                                    'maximumLength' => 45,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        [
            'id'               => 5,
            'name'             => 'instabox',
            'primary'          => 1,
            'type'             => 'main',
            'recipientOptions' => [
                'packageTypes' => [
                    [
                        'id'            => 1,
                        'name'          => 'package',
                        'deliveryTypes' => [
                            [
                                'id'   => 2,
                                'name' => 'standard',
                            ],
                        ],
                        'options'       => [
                            'signature'        => ['type' => 'boolean'],
                            'ageCheck'         => ['type' => 'boolean'],
                            'onlyRecipient'    => ['type' => 'boolean'],
                            'return'           => ['type' => 'boolean'],
                            'sameDayDelivery'  => ['type' => 'boolean'],
                            'largeFormat'      => ['type' => 'boolean'],
                            'labelDescription' => [
                                'type'          => 'string',
                                'minimumLength' => 0,
                                'maximumLength' => 45,
                            ],
                        ],
                        'requirements'  => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 30000,
                            ],
                        ],
                    ],
                    [
                        'id'           => 2,
                        'name'         => 'mailbox',
                        'options'      => [
                            'signature'        => ['type' => 'boolean'],
                            'sameDayDelivery'  => ['type' => 'boolean'],
                            'labelDescription' => [
                                'type'          => 'string',
                                'minimumLength' => 0,
                                'maximumLength' => 45,
                            ],
                        ],
                        'requirements' => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 2000,
                            ],
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [],
        ],
        [
            'id'               => 2,
            'name'             => 'bpost',
            'contractId'       => 10921,
            'primary'          => 0,
            'type'             => 'custom',
            'recipientOptions' => [
                'packageTypes' => [
                    [
                        'id'            => 1,
                        'name'          => 'package',
                        'deliveryTypes' => [
                            [
                                'id'   => 2,
                                'name' => 'standard',
                            ],
                        ],
                        'options'       => [
                            'insurance'            => [
                                'type' => 'integer',
                                'enum' => [
                                    0,
                                    500,
                                ],
                            ],
                            'signature'            => ['type' => 'boolean'],
                            'saturdayDelivery'     => ['type' => 'boolean'],
                            'dropOffAtPostalPoint' => ['type' => 'boolean'],
                            'return'               => ['type' => 'boolean'],
                            'labelDescription'     => [
                                'type'          => 'string',
                                'minimumLength' => 0,
                                'maximumLength' => 45,
                            ],
                        ],
                        'requirements'  => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 30000,
                            ],
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [
                [
                    'packageTypes' => [
                        [
                            'id'            => 1,
                            'name'          => 'package',
                            'deliveryTypes' => [
                                [
                                    'id'   => 2,
                                    'name' => 'standard',
                                ],
                            ],
                            'options'       => [
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
                                'largeFormat'      => ['type' => 'boolean'],
                                'labelDescription' => [
                                    'type'          => 'string',
                                    'minimumLength' => 0,
                                    'maximumLength' => 45,
                                ],
                            ],
                        ],
                        [
                            'id'      => 2,
                            'name'    => 'mailbox',
                            'options' => [
                                'labelDescription' => [
                                    'type'          => 'string',
                                    'minimumLength' => 0,
                                    'maximumLength' => 45,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
        [
            'id'               => 4,
            'name'             => 'dpd',
            'contractId'       => 10932621,
            'primary'          => 0,
            'type'             => 'custom',
            'recipientOptions' => [
                'packageTypes' => [
                    [
                        'id'            => 1,
                        'name'          => 'package',
                        'deliveryTypes' => [
                            [
                                'id'   => 2,
                                'name' => 'standard',
                            ],
                        ],
                        'options'       => [
                            'insurance'        => [
                                'type' => 'integer',
                                'enum' => [
                                    520,
                                ],
                            ],
                            'labelDescription' => [
                                'type'          => 'string',
                                'minimumLength' => 0,
                                'maximumLength' => 45,
                            ],
                        ],
                        'requirements'  => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 31500,
                            ],
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [],
        ],
        [
            'id'               => 6,
            'name'             => 'bpost',
            'primary'          => 1,
            'type'             => 'main',
            'recipientOptions' => [
                'packageTypes' => [
                    [
                        'id'            => 1,
                        'name'          => 'package',
                        'deliveryTypes' => [
                            [
                                'id'   => 2,
                                'name' => 'standard',
                            ],
                        ],
                        'options'       => [
                            'insurance'        => [
                                'type' => 'integer',
                                'enum' => [
                                    520,
                                ],
                            ],
                            'labelDescription' => [
                                'type'          => 'string',
                                'minimumLength' => 0,
                                'maximumLength' => 45,
                            ],
                        ],
                        'requirements'  => [
                            'weight' => [
                                'type'    => 'integer',
                                'minimum' => 1,
                                'maximum' => 31500,
                            ],
                        ],
                    ],
                ],
            ],
            'returnOptions'    => [],
        ],
    ],
];

return $correctVersion + [

        CarrierPostNL::NAME => [
            'class'          => CarrierPostNL::class,
            'validator'      => PostNLShipmentValidator::class,
            'home_countries' => [CountryCodes::CC_NL],
            'delivery_types' => DeliveryOptions::DELIVERY_TYPES_NAMES,
        ],

        CarrierDPD::NAME => [
            'class'          => CarrierDPD::class,
            'validator'      => DPDShipmentValidator::class,
            'home_countries' => [CountryCodes::CC_NL],
            'delivery_types' => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
            ],
        ],

        CarrierInstabox::NAME => [
            'class'          => CarrierInstabox::class,
            'validator'      => InstaboxShipmentValidator::class,
            'home_countries' => [AbstractConsignment::CC_NL],
            'delivery_types' => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
            ],
        ],

        CarrierBpost::NAME => [
            'class'          => CarrierBpost::class,
            'validator'      => BpostShipmentValidator::class,
            'home_countries' => [AbstractConsignment::CC_NL],
            'delivery_types' => [
                DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
            ],
        ],
    ];

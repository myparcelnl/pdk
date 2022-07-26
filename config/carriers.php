<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
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
            'id'            => Carrier::CARRIER_POSTNL_ID,
            'name'          => Carrier::CARRIER_POSTNL_NAME,
            'primary'       => 1,
            'type'          => Carrier::TYPE_VALUE_MAIN,
            'options'       => [
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
                        'signature'        => true,
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
                        'ageCheck'         => true,
                        'onlyRecipient'    => true,
                        'return'           => true,
                        'sameDayDelivery'  => true,
                        'largeFormat'      => true,
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
                        'labelDescription' => [
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
                    'shipmentOptions' => [],
                    'requirements'    => [],
                ],
                [
                    'packageType'     => [
                        'id'   => 4,
                        'name' => 'digital_stamp',
                    ],
                    'shipmentOptions' => [
                        'weightClasses' => [
                            [0, 20],
                            [20, 50],
                            [50, 100],
                            [100, 350],
                            [350, 2000],
                        ],
                    ],
                ],
            ],
            'returnOptions' => [
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
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
        ],
        [
            'id'            => Carrier::CARRIER_INSTABOX_ID,
            'name'          => Carrier::CARRIER_INSTABOX_NAME,
            'primary'       => 1,
            'type'          => Carrier::TYPE_VALUE_MAIN,
            'options'       => [
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
                        'signature'        => true,
                        'ageCheck'         => true,
                        'onlyRecipient'    => true,
                        'return'           => true,
                        'sameDayDelivery'  => true,
                        'largeFormat'      => true,
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
                        'signature'        => true,
                        'sameDayDelivery'  => true,
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
            'returnOptions' => [],
        ],
        [
            'id'             => Carrier::CARRIER_BPOST_ID,
            'name'           => Carrier::CARRIER_BPOST_NAME,
            'subscriptionId' => 10921,
            'primary'        => 0,
            'type'           => Carrier::TYPE_VALUE_CUSTOM,
            'options'        => [
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
                        'insurance'            => [
                            'type' => 'integer',
                            'enum' => [
                                0,
                                500,
                            ],
                        ],
                        'signature'            => true,
                        'saturdayDelivery'     => true,
                        'dropOffAtPostalPoint' => true,
                        'return'               => true,
                        'labelDescription'     => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
            'returnOptions'  => [
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
                        'signature'        => true,
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
                        'largeFormat'      => true,
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
        ],
        [
            'id'             => Carrier::CARRIER_DPD_ID,
            'name'           => Carrier::CARRIER_DPD_NAME,
            'subscriptionId' => 10932621,
            'primary'        => 0,
            'type'           => Carrier::TYPE_VALUE_CUSTOM,
            'options'        => [
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
                        'insurance'        => [
                            'type' => 'integer',
                            'enum' => [
                                520,
                            ],
                        ],
                        'labelDescription' => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
            'returnOptions'  => [],
        ],
        [
            'id'            => Carrier::CARRIER_BPOST_ID,
            'name'          => Carrier::CARRIER_BPOST_NAME,
            'primary'       => 1,
            'type'          => Carrier::TYPE_VALUE_MAIN,
            'options'        => [
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
                        'insurance'            => [
                            'type' => 'integer',
                            'enum' => [
                                0,
                                500,
                            ],
                        ],
                        'signature'            => true,
                        'saturdayDelivery'     => true,
                        'dropOffAtPostalPoint' => true,
                        'return'               => true,
                        'labelDescription'     => [
                            'type'      => 'string',
                            'minLength' => 0,
                            'maxLength' => 45,
                        ],
                    ],
                ],
            ],
            'returnOptions'  => [],
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

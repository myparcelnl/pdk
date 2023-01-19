<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

return [
    'description' => 'myparcel/order',
    'carrier'     => [
        [
            'id'           => Carrier::CARRIER_POSTNL_ID,
            'name'         => Carrier::CARRIER_POSTNL_NAME,
            'human'        => 'PostNL',
            'schema'       => 'order/postnl/base',
            'shippingZone' => [
                [
                    'name'        => CountryService::CC_NL,
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema'       => 'order/postnl/nl_package',
                            'deliveryType' => [
                                [
                                    'id'     => DeliveryOptions::DELIVERY_TYPE_MORNING_ID,
                                    'name'   => DeliveryOptions::DELIVERY_TYPE_MORNING_NAME,
                                    'schema' => 'order/postnl/morning_evening',
                                ],
                                [
                                    'id'     => DeliveryOptions::DELIVERY_TYPE_EVENING_ID,
                                    'name'   => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                                    'schema' => 'order/postnl/morning_evening',
                                ],
                            ],
                        ],
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                            'schema' => 'order/postnl/mailbox',
                        ],
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                            'schema' => 'order/postnl/letter',
                        ],
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_DIGITAL_STAMP_NAME,
                            'schema' => 'order/postnl/digital_stamp',
                        ],
                    ],
                ],
                [
                    'name'        => CountryService::CC_BE,
                    'schema'      => [
                        'properties' => [
                            'deliveryOptions' => [
                                'properties' => [
                                    'packageType' => [
                                        'enum' => [
                                            DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                                            DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema'       => 'order/postnl/be_package',
                            'deliveryType' => [
                                [
                                    'id'     => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                    'name'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                    'schema' => 'order/postnl/be_package_pickup',
                                ],
                            ],
                        ],
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                            'schema' => 'order/postnl/letter',
                        ],
                    ],
                ],
                [
                    'name'        => CountryService::ZONE_EU,
                    'packageType' => [
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema' => 'order/postnl/eu_package',
                        ],
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                            'schema' => 'order/postnl/letter',
                        ],
                    ],
                ],
                [
                    'name'        => CountryService::ZONE_ROW,
                    'schema'      => 'customs_declaration',
                    'packageType' => [
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema' => 'order/postnl/row_package',
                        ],
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                            'schema' => 'order/postnl/letter',
                        ],
                    ],
                ],
            ],
        ],
        [
            'id'           => Carrier::CARRIER_INSTABOX_ID,
            'name'         => Carrier::CARRIER_INSTABOX_NAME,
            'human'        => 'Instabox',
            'schema'       => 'order/instabox/base',
            'shippingZone' => [
                [
                    'name'        => CountryService::CC_NL,
                    'packageType' => [
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema' => 'order/instabox/nl_package',
                        ],
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                            'schema' => 'order/instabox/mailbox',
                        ],
                    ],
                ],
            ],
        ],
    ],
];

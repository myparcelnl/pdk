<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

return [
    'description' => 'myparcel/order',
    'carrier'     => [
        [
            'id'           => CarrierOptions::CARRIER_POSTNL_ID,
            'name'         => CarrierOptions::CARRIER_POSTNL_ID,
            'human'        => 'PostNL',
            'schema'       => 'order/postnl/base',
            'shippingZone' => [
                [
                    'cc'          => CountryService::CC_NL,
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema'       => 'order/postnl/nl_package',
                            'deliveryType' => [
                                [
                                    'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                    'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                ],
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
                                [
                                    'id'     => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                    'name'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                ],
                            ],
                        ],
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_MAILBOX_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                            'schema'       => 'order/postnl/mailbox',
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
                            'schema'       => 'order/postnl/letter',
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
                            'schema'       => 'order/postnl/digital_stamp',
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
                    'cc'          => CountryService::CC_BE,
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
                                    'id'   => DeliveryOptions::DELIVERY_TYPE_STANDARD_ID,
                                    'name' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                ],
                                [
                                    'id'     => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                                    'name'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                                    'schema' => 'order/postnl/be_package_pickup',
                                ],
                            ],
                        ],
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_LETTER_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_LETTER_NAME,
                            'schema'       => 'order/postnl/letter',
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
                    'cc'          => CountryService::ZONE_EU,
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema'       => 'order/postnl/eu_package',
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
                            'schema'       => 'order/postnl/letter',
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
                    'cc'          => CountryService::ZONE_ROW,
                    'schema' => [
                        'required' => [
                            'customsDeclaration',
                        ],
                    ],
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema'       => 'order/postnl/row_package',
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
                            'schema'       => 'order/postnl/letter',
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
            'name'         => CarrierOptions::CARRIER_INSTABOX_ID,
            'human'        => 'PostNL',
            'schema'       => 'order/instabox/base',
            'shippingZone' => [
                [
                    'cc'          => CountryService::CC_NL,
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema'       => 'order/instabox/nl_package',
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
                            'schema'       => 'order/instabox/mailbox',
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
];

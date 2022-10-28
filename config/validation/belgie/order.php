<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

return [
    'carrier' => [
        [
            'id'           => CarrierOptions::CARRIER_POSTNL_ID,
            'name'         => CarrierOptions::CARRIER_POSTNL_NAME,
            'human'        => 'PostNL',
            'schema'       => 'order/postnl/base',
            'shippingZone' => [
                [
                    'cc'          => CountryService::CC_BE,
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
                    ],
                ],
                [
                    'cc'          => CountryService::CC_NL,
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema'       => 'order/postnl/nl_package',
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
                    ],
                ],
                [
                    'cc'          => CountryService::ZONE_ROW,
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
            'id'           => CarrierOptions::CARRIER_BPOST_ID,
            'name'         => CarrierOptions::CARRIER_BPOST_NAME,
            'human'        => 'Bpost',
            'schema'       => 'order/bpost/base',
            'shippingZone' => [
                [
                    'cc'          => CountryService::CC_BE,
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema'       => 'order/bpost/be_package',
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
                    'cc'          => CountryService::CC_NL,
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema'       => 'order/bpost/nl_package',
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
                            'schema'       => 'order/bpost/eu_package',
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
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema'       => 'order/bpost/row_package',
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
            'id'           => CarrierOptions::CARRIER_DPD_ID,
            'name'         => CarrierOptions::CARRIER_DPD_NAME,
            'human'        => 'DPD',
            'schema'       => 'order/dpd/base',
        ],
    ],
];

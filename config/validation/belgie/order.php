<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

return [
    'carrier' => [
        [
            'id'           => CarrierOptions::CARRIER_BPOST_ID,
            'name'         => CarrierOptions::CARRIER_BPOST_NAME,
            'human'        => 'Bpost',
            'schema'       => 'order/bpost/base',
            'shippingZone' => [
                [
                    'name' => CountryCodes::CC_BE,
                    'note' => 'Is validated using base',
                ],
                [
                    'name'        => CountryCodes::CC_NL,
                    'packageType' => [
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema' => 'order/bpost/eu_package',
                        ],
                    ],
                ],
                [
                    'name'        => CountryCodes::ZONE_EU,
                    'packageType' => [
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema' => 'order/bpost/eu_package',
                        ],
                    ],
                ],
                [
                    'name'        => CountryCodes::ZONE_ROW,
                    'schema'      => 'customs_declaration',
                    'packageType' => [
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema' => 'order/bpost/row_package',
                        ],
                    ],
                ],
            ],
        ],
        [
            'id'     => CarrierOptions::CARRIER_DPD_ID,
            'name'   => CarrierOptions::CARRIER_DPD_NAME,
            'human'  => 'DPD',
            'schema' => 'order/dpd/base',
        ],
        [
            'id'           => CarrierOptions::CARRIER_POSTNL_ID,
            'name'         => CarrierOptions::CARRIER_POSTNL_NAME,
            'human'        => 'PostNL',
            'schema'       => 'order/postnl/base',
            'shippingZone' => [
                [
                    'name'        => CountryCodes::CC_BE,
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
                    'name'        => CountryCodes::CC_NL,
                    'packageType' => [
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema' => 'order/postnl/nl_package',
                        ],
                    ],
                ],
                [
                    'name'        => CountryCodes::ZONE_EU,
                    'packageType' => [
                        [
                            'id'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'   => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'schema' => 'order/postnl/eu_package',
                        ],
                    ],
                ],
                [
                    'name'        => CountryCodes::ZONE_ROW,
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
    ],
];

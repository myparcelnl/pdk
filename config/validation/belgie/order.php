<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;

return [
    'carrier' => [
        [
            'id'           => RefTypesCarrier::BPOST,
            'name'         => RefTypesCarrierV2::BPOST,
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
            'id'     => RefTypesCarrier::DPD,
            'name'   => RefTypesCarrierV2::DPD,
            'human'  => 'DPD',
            'schema' => 'order/dpd/base',
        ],
        [
            'id'           => RefTypesCarrier::POSTNL,
            'name'         => RefTypesCarrierV2::POSTNL,
            'human'        => 'PostNL',
            'schema'       => 'order/postnl/base',
            'shippingZone' => [
                [
                    'name'        => CountryCodes::CC_BE,
                    'packageType' => [
                        [
                            'id'           => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                            'name'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                            'note'         => 'Is validated using base',
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

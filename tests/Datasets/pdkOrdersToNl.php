<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;

dataset('pdkOrdersDomestic', [
    'single order' => function () {
        return [
            new PdkOrder([
                'externalIdentifier' => '247',
                'billingAddress'     => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'address1'    => 'Antareslaan 31',
                    'email'       => 'myparcel@myparcel.nl',
                    'phone'       => '0612345678',
                ],
                'shippingAddress'    => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'address1'    => 'Antareslaan 31',
                    'email'       => 'myparcel@myparcel.nl',
                    'phone'       => '0612345678',
                ],
                'deliveryOptions'    => [
                    'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                    'shipmentOptions' => [
                        'signature' => true,
                    ],
                ],
                'notes'              => [
                    [
                        'apiIdentifier' => '90001',
                        'author'        => OrderNote::AUTHOR_WEBSHOP,
                        'note'          => 'test note',
                        'createdAt'     => '2023-01-01 12:00:00',
                        'updatedAt'     => '2023-01-01 12:00:00',
                    ],
                    [
                        'author'    => OrderNote::AUTHOR_CUSTOMER,
                        'note'      => 'hello',
                        'createdAt' => '2023-01-01 12:00:00',
                        'updatedAt' => '2023-01-02 12:00:00',
                    ],
                ],
                'lines'              => [
                    [
                        'quantity' => 2,
                        'product'  => [
                            'externalIdentifier' => '123',
                            'sku'                => 'test',
                            'name'               => 'test',
                            'price'              => [
                                'amount'   => 1000,
                                'currency' => 'EUR',
                            ],
                        ],
                    ],
                ],
            ]),
        ];
    },

    'two orders' => function () {
        return [
            new PdkOrder([
                'externalIdentifier' => '245',
                'deliveryOptions'    => [
                    'carrier'     => Carrier::CARRIER_POSTNL_NAME,
                    'packageType' => 'package',
                    'labelAmount' => 2,
                ],
                'billingAddress'     => [
                    'cc'         => CountryCodes::CC_NL,
                    'address1'   => 'Antareslaan 31',
                    'postalCode' => '2132JE',
                    'city'       => 'Hoofddorp',
                    'person'     => 'Felicia Parcel',
                    'phone'      => '0612345678',
                ],
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'address1'   => 'Pietjestraat 35',
                    'postalCode' => '2771BW',
                    'city'       => 'Bikinibroek',
                    'email'      => 'test@myparcel.nl',
                ],
            ]),

            new PdkOrder([
                'externalIdentifier' => '247',
                'shippingAddress'    => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'address1'    => 'Antareslaan 31',
                    'email'       => 'test@myparcel.nl',
                ],
                'deliveryOptions'    => [
                    'carrier'      => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType' => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                ],
                'notes'              => [
                    [
                        'author'    => OrderNote::AUTHOR_CUSTOMER,
                        'note'      => 'test note from customer',
                        'createdAt' => '2023-01-01 12:00:00',
                        'updatedAt' => '2023-01-01 18:00:00',
                    ],
                ],
            ]),
        ];
    },

    'carrier dhl for you' => function () {
        return [
            new PdkOrder([
                'externalIdentifier' => '249',
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'address1'   => 'Pietjestraat 35',
                    'postalCode' => '2771BW',
                    'city'       => 'Bikinibroek',
                    'email'      => 'test@myparcel.nl',
                ],
                'deliveryOptions'    => [
                    'carrier' => Carrier::CARRIER_DHL_FOR_YOU_NAME,
                ],
            ]),
        ];
    },

    'various delivery options' => function () {
        return [
            new PdkOrder([
                'externalIdentifier' => '250',
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'address1'   => 'Pietjestraat 35',
                    'postalCode' => '2771BW',
                    'city'       => 'Bikinibroek',
                ],
                'lines'              => [
                    [
                        'quantity' => 5,
                        'product'  => [
                            'externalIdentifier' => '456',
                            'sku'                => 'test123',
                            'name'               => 'test123',
                            'price'              => [
                                'amount'   => 4000,
                                'currency' => 'EUR',
                            ],
                        ],
                    ],
                ],
                'deliveryOptions'    => [
                    'carrier'     => Carrier::CARRIER_POSTNL_NAME,
                    'packageType' => DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME,
                ],
            ]),

            new PdkOrder([
                'externalIdentifier' => '250',
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'address1'   => 'Pietjestraat 35',
                    'postalCode' => '2771BW',
                    'city'       => 'Bikinibroek',
                ],
                'lines'              => [
                    [
                        'quantity' => 2,
                        'product'  => [
                            'externalIdentifier' => '123',
                            'sku'                => 'test',
                            'name'               => 'test',
                            'price'              => [
                                'amount'   => 1000,
                                'currency' => 'EUR',
                            ],
                        ],
                    ],
                ],
                'deliveryOptions'    => [
                    'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_EVENING_NAME,
                    'date'            => '2077-10-23 09:47:51',
                    'shipmentOptions' => [
                        ShipmentOptions::AGE_CHECK      => false,
                        ShipmentOptions::ONLY_RECIPIENT => true,
                        ShipmentOptions::SIGNATURE      => true,
                    ],
                ],
            ]),

            new PdkOrder([
                'externalIdentifier' => '251',
                'shippingAddress'    => [
                    'cc'         => CountryCodes::CC_NL,
                    'address1'   => 'Pietjestraat 35',
                    'postalCode' => '2771BW',
                    'city'       => 'Bikinibroek',
                ],
                'lines'              => [
                    [
                        'quantity' => 2,
                        'product'  => [
                            'externalIdentifier' => '123',
                            'sku'                => 'test',
                            'name'               => 'test',
                            'price'              => [
                                'amount'   => 1000,
                                'currency' => 'EUR',
                            ],
                        ],
                    ],
                ],
                'deliveryOptions'    => [
                    'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                    'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                    'shipmentOptions' => [
                        ShipmentOptions::AGE_CHECK         => true,
                        ShipmentOptions::INSURANCE         => true,
                        ShipmentOptions::LABEL_DESCRIPTION => true,
                        ShipmentOptions::LARGE_FORMAT      => true,
                        ShipmentOptions::ONLY_RECIPIENT    => true,
                        ShipmentOptions::RETURN            => true,
                        ShipmentOptions::SIGNATURE         => true,
                    ],
                ],
            ]),
        ];
    },
]);

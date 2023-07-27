<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

dataset('fulfilmentOrders', [
    'one order containing many attributes' => [
        [
            [
                'invoiceAddress' => null,
                'language'       => null,
                'orderDate'      => '2022-08-22 00:00:00',
                'orderLines'     => [
                    [
                        'uuid'          => '1234',
                        'quantity'      => 1,
                        'price'         => 250,
                        'vat'           => 10,
                        'priceAfterVat' => 260,
                        'product'       => [
                            'uuid'               => '12345',
                            'sku'                => '018234',
                            'ean'                => '018234',
                            'externalIdentifier' => '018234',
                            'name'               => 'Paarse stofzuiger',
                            'description'        => 'Een paars object waarmee stof opgezogen kan worden',
                            'width'              => null,
                            'length'             => null,
                            'height'             => null,
                            'weight'             => 3500,
                        ],
                    ],
                ],
                'price'          => 260,
                'shipment'       => [
                    'apiKey'             => '123',
                    'carrier'            => [
                        'id' => Carrier::CARRIER_POSTNL_ID,
                    ],
                    'customsDeclaration' => [
                        'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                        'invoice'  => '25',
                        'items'    => [
                            [
                                'amount'         => 1,
                                'classification' => 5256,
                                'country'        => CountryCodes::CC_BE,
                                'description'    => 'Vlaamse Patatekes',
                                'itemValue'      => ['amount' => 5000, 'currency' => 'EUR'],
                                'weight'         => 200,
                            ],
                            [
                                'amount'         => 1,
                                'classification' => 9221,
                                'country'        => CountryCodes::CC_FR,
                                'description'    => 'Omelette du Fromage',
                                'itemValue'      => ['amount' => 10000, 'currency' => 'EUR'],
                                'weight'         => 350,
                            ],
                        ],
                    ],
                    'deliveryOptions'    => [
                        'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                        'date'            => '2077-10-23 09:47:51',
                        'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                        'packageType'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        'pickupLocation'  => null,
                        'shipmentOptions' => [
                            'ageCheck'         => true,
                            'insurance'        => 0,
                            'labelDescription' => null,
                            'largeFormat'      => false,
                            'onlyRecipient'    => false,
                            'return'           => false,
                            'sameDayDelivery'  => false,
                            'signature'        => true,
                        ],
                    ],
                    'dropOffPoint'       => null,
                    'physicalProperties' => [
                        'weight' => 3500,
                    ],
                    'recipient'          => [
                        'cc'         => 'NL',
                        'city'       => 'Hoofddorp',
                        'person'     => 'Jaap Krekel',
                        'postalCode' => '2132JE',
                        'address1'   => 'Antareslaan 31',
                    ],
                    'sender'             => [
                        'cc'         => 'NL',
                        'city'       => 'Amsterdam',
                        'person'     => 'Willem Wever',
                        'postalCode' => '4164ZF',
                        'address1'   => 'Werf 2',
                    ],
                ],
                'shopId'         => null,
                'status'         => null,
                'type'           => null,
                'updatedAt'      => null,
                'uuid'           => null,
                'vat'            => null,
            ],
        ],
    ],

    'one order with pickup' => [
        [
            [
                'invoiceAddress' => null,
                'language'       => null,
                'orderDate'      => '2022-08-22 00:00:00',
                'orderLines'     => [
                    [
                        'uuid'          => '1234',
                        'quantity'      => 1,
                        'price'         => 250,
                        'vat'           => 10,
                        'priceAfterVat' => 260,
                        'product'       => [
                            'uuid'               => '12345',
                            'sku'                => '018234',
                            'ean'                => '018234',
                            'externalIdentifier' => '018234',
                            'name'               => 'Paarse stofzuiger',
                            'description'        => 'Een paars object waarmee stof opgezogen kan worden',
                            'width'              => null,
                            'length'             => null,
                            'height'             => null,
                            'weight'             => 3500,
                        ],
                    ],
                ],
                'price'          => 260,
                'shipment'       => [
                    'apiKey'             => '123',
                    'carrier'            => [
                        'id' => Carrier::CARRIER_POSTNL_ID,
                    ],
                    'deliveryOptions'    => [
                        'carrier'         => Carrier::CARRIER_POSTNL_NAME,
                        'date'            => '2077-10-23 09:47:51',
                        'deliveryType'    => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                        'packageType'     => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                        'pickupLocation'  => [
                            'locationCode' => 0172,
                        ],
                        'shipmentOptions' => [
                            'ageCheck'         => true,
                            'insurance'        => 0,
                            'labelDescription' => null,
                            'largeFormat'      => false,
                            'onlyRecipient'    => false,
                            'return'           => false,
                            'sameDayDelivery'  => false,
                            'signature'        => true,
                        ],
                    ],
                    'dropOffPoint'       => null,
                    'physicalProperties' => [
                        'weight' => 3500,
                    ],
                    'recipient'          => [
                        'cc'         => 'NL',
                        'city'       => 'Hoofddorp',
                        'person'     => 'Jaap Krekel',
                        'postalCode' => '2132JE',
                        'address1'   => 'Antareslaan 31',
                    ],
                    'sender'             => [
                        'cc'         => 'NL',
                        'city'       => 'Amsterdam',
                        'person'     => 'Willem Wever',
                        'postalCode' => '4164ZF',
                        'address1'   => 'Werf 2',
                    ],
                ],
                'shopId'         => null,
                'status'         => null,
                'type'           => null,
                'updatedAt'      => null,
                'uuid'           => null,
                'vat'            => null,
            ],
        ],
    ],
]);

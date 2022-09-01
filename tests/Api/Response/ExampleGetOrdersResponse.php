<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;

class ExampleGetOrdersResponse extends ExampleJsonResponse
{
    /**
     * @return array
     */
    public function getContent(): array
    {
        return [
            'data' => [
                'orders' => [
                    [
                        'invoice_address' => [
                            'cc'   => 'NL',
                            'city' => 'Boskoop',
                        ],
                        'language'        => null,
                        'order_date'      => '2022-08-22 00:00:00',
                        'order_lines'     => [
                            [
                                'uuid'            => '1234',
                                'quantity'        => 1,
                                'price'           => 250,
                                'vat'             => 10,
                                'price_after_vat' => 260,
                                'product'         => [
                                    'uuid'                => '12345',
                                    'sku'                 => '018234',
                                    'ean'                 => '018234',
                                    'external_identifier' => '018234',
                                    'name'                => 'Paarse stofzuiger',
                                    'description'         => 'Een paars object waarmee stof opgezogen kan worden',
                                    'width'               => null,
                                    'length'              => null,
                                    'height'              => null,
                                    'weight'              => 3500,
                                ],
                            ],
                        ],
                        'price'           => 260,
                        'shipment'        => [
                            'carrier_id'          => CarrierOptions::CARRIER_POSTNL_ID,
                            'customs_declaration' => null,
                            'options'             => [
                                'date'              => '2022-08-22 00:00:00',
                                'delivery_type'     => DeliveryOptions::DELIVERY_TYPE_STANDARD_NAME,
                                'package_type'      => DeliveryOptions::PACKAGE_TYPE_PACKAGE_NAME,
                                'age_check'         => 1,
                                'insurance'         => [
                                    'amount'   => 0,
                                    'currency' => 'EUR',
                                ],
                                'label_description' => null,
                                'large_format'      => 0,
                                'only_recipient'    => 0,
                                'return'            => 0,
                                'same_day_delivery' => 0,
                                'signature'         => 1,
                            ],
                            'dropOffPoint'        => null,
                            'physicalProperties'  => [
                                'weight' => 3500,
                            ],
                            'recipient'           => [
                                'cc'         => 'NL',
                                'city'       => 'Hoofddorp',
                                'person'     => 'Jaap Krekel',
                                'postalCode' => '2132JE',
                                'street'     => 'Antareslaan 31',
                            ],
                            'sender'              => [
                                'cc'         => 'NL',
                                'city'       => 'Amsterdam',
                                'number'     => '2',
                                'person'     => 'Willem Wever',
                                'postalCode' => '4164ZF',
                                'street'     => 'Werf',
                            ],
                        ],
                        'shopId'          => null,
                        'status'          => null,
                        'type'            => null,
                        'updatedAt'       => null,
                        'uuid'            => null,
                        'vat'             => null,
                    ],
                ],
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetOrdersResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'uuid'                          => '1ed6c2c8-c15a-68a6-96b4-7ffb046c7237',
                'fulfilment_partner_identifier' => null,
                'external_identifier'           => '1025',
                'order_date'                    => '2022-11-24 20:16:45',
                'invoice_address'               => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'number'      => null,
                    'person'      => 'Felicia Parcel',
                    'postal_code' => '2132 JE',
                    'street'      => 'Antareslaan 31',
                ],
                'language'                      => 'nl',
                'account_id'                    => 123456,
                'shop_id'                       => 122377,
                'type'                          => 'consumer',
                'price'                         => null,
                'vat'                           => null,
                'price_after_vat'               => null,
                'status'                        => 'processed',
                'shipment'                      => [
                    'recipient'           => [
                        'cc'          => 'NL',
                        'city'        => 'Hoofddorp',
                        'number'      => '31',
                        'person'      => 'Felicia Parcel',
                        'postal_code' => '2132 JE',
                        'street'      => 'Antareslaan',
                    ],
                    'pickup'              => null,
                    'drop_off_point'      => null,
                    'options'             => [
                        'package_type'      => 2,
                        'delivery_type'     => 2,
                        'delivery_date'     => '2022-11-24 20:16:50',
                        'signature'         => 0,
                        'only_recipient'    => 0,
                        'age_check'         => 0,
                        'large_format'      => 0,
                        'return'            => 0,
                        'label_description' => '#1025',
                    ],
                    'physical_properties' => [
                        'weight' => 35,
                    ],
                    'customs_declaration' => null,
                    'carrier'             => 1,
                    'contract_id'         => null,
                ],
                'order_lines'                   => [
                    [
                        'uuid'            => '4040ae40-d54a-4a64-aa5a-1ebd73680fdc',
                        'quantity'        => 2,
                        'price'           => 519,
                        'vat'             => 276,
                        'price_after_vat' => 795,
                        'product'         => [
                            'external_identifier' => '43597032554725',
                            'name'                => 'Hendel (per stuk)',
                            'description'         => 'Zilver',
                            'width'               => 0,
                            'height'              => 0,
                            'length'              => 0,
                            'weight'              => 6,
                        ],
                        'instructions'    => null,
                        'shippable'       => true,
                    ],
                    [
                        'uuid'            => 'd9d4de59-1b88-467e-bba5-fdacb8bf27b3',
                        'quantity'        => 1,
                        'price'           => 1483,
                        'vat'             => 312,
                        'price_after_vat' => 1795,
                        'product'         => [
                            'external_identifier' => '12746352738812',
                            'name'                => 'Stofzuigerzak - Met diamantjes',
                            'description'         => '1 per verpakking',
                            'width'               => 0,
                            'height'              => 0,
                            'length'              => 0,
                            'weight'              => 21,
                        ],
                        'instructions'    => null,
                        'shippable'       => true,
                    ],
                    [
                        'uuid'            => '84bbe3b0-da17-4de6-8233-e6555bdf8be3',
                        'quantity'        => 2,
                        'price'           => 583,
                        'vat'             => 311,
                        'price_after_vat' => 894,
                        'product'         => [
                            'external_identifier' => '32436582546543',
                            'name'                => 'Stofzuigerzak',
                            'description'         => '1 per verpakking',
                            'width'               => 0,
                            'height'              => 0,
                            'length'              => 0,
                            'weight'              => 8,
                        ],
                        'instructions'    => null,
                        'shippable'       => true,
                    ],
                ],
                'order_shipments'               => [
                    [
                        'uuid'                         => '45c19d07-4d47-48df-989f-a01b9d988d7e',
                        'external_shipment_identifier' => '3SMYPA098753425',
                        'shipment_id'                  => 83729991,
                        'shipment'                     => [
                            'id'                           => 83729991,
                            'parent_id'                    => null,
                            'account_id'                   => 123456,
                            'shop_id'                      => 122377,
                            'shipment_type'                => 1,
                            'recipient'                    => [
                                'cc'                     => 'NL',
                                'postal_code'            => '2132 JE',
                                'city'                   => 'Hoofddorp',
                                'street'                 => 'Antareslaan',
                                'street_additional_info' => '',
                                'number'                 => '31',
                                'number_suffix'          => '',
                                'person'                 => 'Felicia Parcel',
                                'email'                  => '',
                                'phone'                  => '',
                            ],
                            'sender'                       => [
                                'email'                  => 'felicia@myparcel.nl',
                                'phone'                  => '0612345678',
                                'company'                => 'MyParcel',
                                'postal_code'            => '2132 JE',
                                'number'                 => '31',
                                'street'                 => 'Antareslaan',
                                'street_additional_info' => '',
                                'city'                   => 'MyParcel',
                                'cc'                     => 'NL',
                                'person'                 => 'Felicia',
                                'number_suffix'          => '',
                            ],
                            'status'                       => 7,
                            'options'                      => [
                                'package_type'             => 2,
                                'collect'                  => 0,
                                'only_recipient'           => 0,
                                'signature'                => 0,
                                'return'                   => 0,
                                'insurance'                => [
                                    'amount'   => 0,
                                    'currency' => 'EUR',
                                ],
                                'large_format'             => 0,
                                'age_check'                => 0,
                                'saturday_delivery'        => 0,
                                'drop_off_at_postal_point' => 0,
                                'label_description'        => '#1025',
                                'delivery_type'            => 2,
                            ],
                            'general_settings'             => [
                                'save_recipient_address' => 0,
                                'tracktrace'             => [
                                    'delivery_notification'            => 0,
                                    'send_track_trace_emails'          => 0,
                                    'email_on_handed_to_courier'       => 1,
                                    'carrier_email_basic_notification' => 1,
                                    'bcc'                              => 0,
                                    'from_address_email'               => 'felicia@myparcel.nl',
                                    'from_address_company'             => 'MyParcel',
                                    'bcc_email'                        => '',
                                ],
                            ],
                            'pickup'                       => null,
                            'customs_declaration'          => null,
                            'physical_properties'          => [
                                'carrier_height' => 20,
                                'carrier_width'  => 105,
                                'carrier_length' => 150,
                                'carrier_volume' => 315,
                                'carrier_weight' => 40,
                                'height'         => 0,
                                'width'          => 0,
                                'length'         => 0,
                                'volume'         => 0,
                                'weight'         => 1000,
                            ],
                            'created'                      => '2022-11-25 13:08:22',
                            'modified'                     => '2022-12-05 01:03:51',
                            'reference_identifier'         => '',
                            'created_by'                   => 28288,
                            'modified_by'                  => 28288,
                            'transaction_status'           => 'unpaid',
                            'drop_off_point'               => null,
                            'hidden'                       => false,
                            'order_shipment_identifier'    => '45c19d07-4d47-48df-989f-a01b9d988d7e',
                            'price'                        => [
                                'amount'   => 425,
                                'currency' => 'EUR',
                            ],
                            'barcode'                      => '3SMYPA098753425',
                            'region'                       => 'NL',
                            'external_provider'            => null,
                            'external_provider_id'         => null,
                            'payment_status'               => 'unpaid',
                            'carrier_id'                   => 1,
                            'platform_id'                  => 1,
                            'origin'                       => 'pdk',
                            'user_agent'                   => 'MyParcelNL-pdk/1.0.0',
                            'secondary_shipments'          => [
                            ],
                            'collection_contact'           => null,
                            'multi_collo_main_shipment_id' => null,
                            'external_identifier'          => '3SMYPA098753425',
                            'delayed'                      => false,
                            'delivered'                    => true,
                            'contract_id'                  => 1,
                        ],
                        'shipped_items'                => [
                            [
                                'quantity'              => 1,
                                'order_line_identifier' => 'd9d4de59-1b88-467e-bba5-fdacb8bf27b3',
                            ],
                            [
                                'quantity'              => 2,
                                'order_line_identifier' => '4040ae40-d54a-4a64-aa5a-1ebd73680fdc',
                            ],
                            [
                                'quantity'              => 2,
                                'order_line_identifier' => '84bbe3b0-da17-4de6-8233-e6555bdf8be3',
                            ],
                        ],
                    ],
                ],
                'created_at'                    => '2022-11-24 20:16:50',
                'updated_at'                    => '2022-11-25 13:09:07',
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'orders';
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Mock\Api\Response;

use MyParcelNL\Pdk\Carrier\Model\Carrier;

class ExampleGetShipmentsFromContractResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'id'                           => 23001,
                'parent_id'                    => null,
                'account_id'                   => 12345,
                'shop_id'                      => 67890,
                'shipment_type'                => 3,
                'recipient'                    => [
                    'cc'          => 'NL',
                    'city'        => 'Hoofddorp',
                    'postal_code' => '2132JE',
                    'street'      => 'Antareslaan',
                    'person'      => 'Jaap Krekel',
                    'number'      => '31',
                ],
                'sender'                       => [
                    'cc'                     => 'NL',
                    'postal_code'            => '2132 JE',
                    'city'                   => 'Hoofddorp',
                    'street'                 => 'Antareslaan',
                    'number'                 => '31',
                    'number_suffix'          => '',
                    'person'                 => 'Bye Felicia',
                    'company'                => 'MyParcel',
                    'email'                  => 'spam@myparcel.nl',
                    'phone'                  => '0612345678',
                    'street_additional_info' => '',
                ],
                'status'                       => 2,
                'options'                      => [
                    'package_type'  => 1,
                    'delivery_type' => 2,
                    'insurance'     => [
                        'amount'   => 0,
                        'currency' => 'EUR',
                    ],
                ],
                'general_settings'             => [
                    'save_recipient_address' => 1,
                    'tracktrace'             => [
                        'carrier_email_basic_notification' => 1,
                        'send_track_trace_emails'          => 1,
                        'email_on_handed_to_courier'       => 0,
                        'bcc'                              => 0,
                        'delivery_notification'            => 0,
                        'bcc_email'                        => 'spam@myparcel.nl',
                        'from_address_email'               => 'spam@myparcel.nl',
                        'from_address_company'             => 'MyParcel',
                    ],
                ],
                'pickup'                       => null,
                'customs_declaration'          => null,
                'physical_properties'          => null,
                'created'                      => '2022-07-25 15:16:31',
                'modified'                     => '2022-07-25 15:16:31',
                'reference_identifier'         => 'my-multicollo-set',
                'created_by'                   => 145,
                'modified_by'                  => 145,
                'transaction_status'           => 'unpaid',
                'drop_off_point'               => null,
                'hidden'                       => 0,
                'price'                        => [
                    'amount'   => 625,
                    'currency' => 'EUR',
                ],
                'barcode'                      => '3SMYPA123456789',
                'region'                       => 'NL',
                'external_provider'            => null,
                'external_provider_id'         => null,
                'payment_status'               => 'unpaid',
                'carrier_id'                   => Carrier::CARRIER_DPD_ID,
                'platform_id'                  => 1,
                'origin'                       => 'postman',
                'user_agent'                   => 'PostmanRuntime/7.29.2',
                'secondary_shipments'          => [
                    [
                        'id'                           => 23002,
                        'parent_id'                    => null,
                        'account_id'                   => 12345,
                        'shop_id'                      => 67890,
                        'shipment_type'                => 3,
                        'recipient'                    => [
                            'cc'          => 'NL',
                            'city'        => 'Hoofddorp',
                            'street'      => 'Antareslaan',
                            'postal_code' => '2132JE',
                            'person'      => 'Jaap Krekel',
                            'number'      => '31',
                        ],
                        'sender'                       => [
                            'cc'                     => 'NL',
                            'postal_code'            => '2132 JE',
                            'city'                   => 'Hoofddorp',
                            'street'                 => 'Antareslaan',
                            'number'                 => '31',
                            'number_suffix'          => '',
                            'person'                 => 'Edie Lemoine',
                            'company'                => 'MyParcel',
                            'email'                  => 'edie@myparcel.nl',
                            'phone'                  => '0617752983',
                            'street_additional_info' => '',
                        ],
                        'status'                       => 2,
                        'options'                      => [
                            'package_type'  => 1,
                            'delivery_type' => 2,
                            'insurance'     => [
                                'amount'   => 0,
                                'currency' => 'EUR',
                            ],
                        ],
                        'general_settings'             => [
                            'save_recipient_address' => 1,
                            'tracktrace'             => [
                                'carrier_email_basic_notification' => 1,
                                'send_track_trace_emails'          => 1,
                                'email_on_handed_to_courier'       => 0,
                                'bcc'                              => 0,
                                'delivery_notification'            => 0,
                                'bcc_email'                        => 'test@myparcel.nl',
                                'from_address_email'               => 'test@myparcel.nl',
                                'from_address_company'             => 'MyParcel',
                            ],
                        ],
                        'pickup'                       => null,
                        'customs_declaration'          => null,
                        'physical_properties'          => null,
                        'created'                      => '2022-07-25 15:16:31',
                        'modified'                     => '2022-07-25 15:16:31',
                        'reference_identifier'         => 'my-multicollo-set',
                        'created_by'                   => 145,
                        'modified_by'                  => 145,
                        'transaction_status'           => 'unpaid',
                        'drop_off_point'               => null,
                        'hidden'                       => 0,
                        'price'                        => [
                            'amount'   => 625,
                            'currency' => 'EUR',
                        ],
                        'barcode'                      => '3SMYPA637827684',
                        'region'                       => 'NL',
                        'external_provider'            => null,
                        'external_provider_id'         => null,
                        'payment_status'               => 'unpaid',
                        'carrier_id'                   => Carrier::CARRIER_DPD_ID,
                        'platform_id'                  => 1,
                        'origin'                       => null,
                        'user_agent'                   => null,
                        'secondary_shipments'          => [],
                        'collection_contact'           => null,
                        'multi_collo_main_shipment_id' => 23001,
                        'external_identifier'          => '3SMYPA637827684',
                        'delayed'                      => false,
                        'delivered'                    => false,
                        'contract_id'                  => 10932623,
                    ],
                ],
                'collection_contact'           => null,
                'multi_collo_main_shipment_id' => 23001,
                'external_identifier'          => '3SMYPA060119130',
                'delayed'                      => false,
                'delivered'                    => false,
                'contract_id'                  => 10932623,
                'link_consumer_portal'         => 'https://demo.myparcel.me/track-trace/demo',
                'partner_tracktraces'          => [],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'shipments';
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetShipmentsResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'id'                           => 7,
                'parent_id'                    => null,
                'account_id'                   => 4,
                'shop_id'                      => 6,
                'shipment_type'                => 1,
                'recipient'                    => [
                    'cc'                     => 'CW',
                    'postal_code'            => '',
                    'city'                   => 'Willemstad',
                    'street'                 => 'Schottegatweg Oost',
                    'street_additional_info' => '',
                    'number_suffix'          => '',
                    'person'                 => 'Joep Meloen',
                    'company'                => 'MyParcel',
                    'email'                  => 'meneer@groenteboer.nl',
                    'phone'                  => '+31699335577',
                    'number'                 => '12',
                ],
                'sender'                       => [
                    'cc'                     => 'NL',
                    'postal_code'            => '2132 JE',
                    'city'                   => 'Hoofddorp',
                    'street'                 => 'Antareslaan',
                    'number'                 => '31',
                    'number_suffix'          => '',
                    'person'                 => 'Denzel Crocker',
                    'company'                => 'Geheime Dingen',
                    'email'                  => 'shop@geheimedingen.nl',
                    'phone'                  => '0612345678',
                    'street_additional_info' => '',
                ],
                'status'                       => 2,
                'options'                      => [
                    'package_type'             => 1,
                    'collect'                  => 0,
                    'only_recipient'           => 0,
                    'signature'                => 0,
                    'return'                   => 0,
                    'insurance'                => [
                        'amount'   => 20000,
                        'currency' => 'EUR',
                    ],
                    'large_format'             => 0,
                    'age_check'                => 0,
                    'saturday_delivery'        => 0,
                    'drop_off_at_postal_point' => 0,
                    'late_drop_off'            => 0,
                    'label_description'        => 'standaard kenmerk',
                    'delivery_type'            => 2,
                ],
                'general_settings'             => [
                    'save_recipient_address' => 1,
                    'tracktrace'             => [
                        'carrier_email_basic_notification' => 1,
                        'send_track_trace_emails'          => 1,
                        'email_on_handed_to_courier'       => 0,
                        'bcc'                              => 1,
                        'delivery_notification'            => 0,
                        'bcc_email'                        => 'edie@myparcel.nl',
                        'from_address_email'               => 'shop@geheimedingen.nl',
                        'from_address_company'             => 'Geheime Dingen',
                        'send_to'                          => 'klant1@myparcel.nl',
                        'send_on'                          => '2021-04-26 02:07:04',
                    ],
                ],
                'pickup'                       => null,
                'customs_declaration'          => [
                    'contents' => 1,
                    'invoice'  => '123456',
                    'weight'   => 3500,
                    'items'    => [
                        0 => [
                            'description'    => 'Product',
                            'amount'         => 1,
                            'weight'         => 500,
                            'item_value'     => [
                                'amount'   => 1000,
                                'currency' => 'EUR',
                            ],
                            'classification' => '123456',
                            'country'        => 'NL',
                        ],
                    ],
                ],
                'physical_properties'          => [
                    'carrier_height' => null,
                    'carrier_width'  => null,
                    'carrier_weight' => null,
                    'carrier_length' => null,
                    'carrier_volume' => null,
                    'height'         => 20,
                    'width'          => 30,
                    'length'         => 40,
                    'weight'         => 3500,
                ],
                'created'                      => '2021-04-26 14:06:45',
                'modified'                     => '2021-05-03 07:42:43',
                'reference_identifier'         => 'GeheimeDingen-1',
                'created_by'                   => 35159,
                'modified_by'                  => 35159,
                'transaction_status'           => 'unpaid',
                'drop_off_point'               => null,
                'hidden'                       => 0,
                'price'                        => [
                    'amount'   => 5390,
                    'currency' => 'EUR',
                ],
                'barcode'                      => 'CV515676839NL',
                'region'                       => 'Wereld',
                'external_provider'            => null,
                'external_provider_id'         => null,
                'payment_status'               => 'unpaid',
                'carrier_id'                   => 1,
                'contract_id'                  => null,
                'platform_id'                  => 1,
                'origin'                       => 'backoffice_shipment_form',
                'user_agent'                   => 'ShipmentForm/',
                'secondary_shipments'          => [
                ],
                'collection_contact'           => null,
                'multi_collo_main_shipment_id' => null,
                'external_identifier'          => 'CV515676839NL',
                'delayed'                      => false,
                'delivered'                    => false,
                'link_consumer_portal'         => null,
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

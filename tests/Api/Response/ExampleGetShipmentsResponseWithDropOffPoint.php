<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetShipmentsResponseWithDropOffPoint extends ExampleJsonResponse
{
    /**
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getContent(): array
    {
        return [
            'data' => [
                'shipments' => [
                    [
                        'id'                           => 136510070,
                        'parent_id'                    => null,
                        'account_id'                   => 170402,
                        'shop_id'                      => 93683,
                        'shipment_type'                => 1,
                        'recipient'                    => [
                            'cc'                     => 'NL',
                            'postal_code'            => '7345AB',
                            'city'                   => 'Wenum Wiesel',
                            'street'                 => 'Wieselsedwarsweg',
                            'street_additional_info' => '',
                            'number'                 => '12',
                            'number_suffix'          => 'c',
                            'person'                 => 'Epke Zonderland',
                            'email'                  => 'test@webshopje.nl',
                            'phone'                  => '0172765435',
                        ],
                        'sender'                       => [
                            'phone'                  => '0606607834',
                            'company'                => 'MyParcel',
                            'cc'                     => 'NL',
                            'postal_code'            => '2132JE',
                            'number'                 => '31',
                            'street'                 => 'Antareslaan',
                            'city'                   => 'Hoofddorp',
                            'email'                  => 'mrparcel@myparcel.nl',
                            'street_additional_info' => '',
                            'person'                 => 'Mister Parcel',
                            'number_suffix'          => '',
                        ],
                        'status'                       => 1,
                        'options'                      => [
                            'package_type'             => 1,
                            'collect'                  => 0,
                            'only_recipient'           => 1,
                            'signature'                => 0,
                            'return'                   => 1,
                            'insurance'                => [
                                'amount'   => 0,
                                'currency' => 'EUR',
                            ],
                            'large_format'             => 1,
                            'same_day_delivery'        => 1,
                            'age_check'                => 1,
                            'saturday_delivery'        => 0,
                            'drop_off_at_postal_point' => 0,
                            'label_description'        => 'Vliegtuig',
                            'delivery_type'            => 2,
                        ],
                        'general_settings'             => [
                            'save_recipient_address' => 1,
                            'tracktrace'             => [
                                'delivery_notification'            => 0,
                                'send_track_trace_emails'          => 0,
                                'email_on_handed_to_courier'       => 1,
                                'carrier_email_basic_notification' => 1,
                                'bcc'                              => 0,
                                'from_address_email'               => 'mark+testaccount@myparcel.nl',
                                'from_address_company'             => 'Mark-test',
                                'bcc_email'                        => '',
                            ],
                        ],
                        'pickup'                       => null,
                        'customs_declaration'          => null,
                        'physical_properties'          => [
                            'weight' => 5000,
                        ],
                        'created'                      => '2022-07-13 11:56:18',
                        'modified'                     => '2022-07-13 11:56:18',
                        'reference_identifier'         => '',
                        'created_by'                   => 82444,
                        'modified_by'                  => 82444,
                        'transaction_status'           => 'unpaid',
                        'drop_off_point'               => [
                            'postal_code'   => '2321 TD',
                            'location_name' => 'Instabox',
                            'city'          => 'Leiden',
                            'street'        => 'Telderskade',
                            'number'        => '2',
                            'number_suffix' => 'H',
                            'cc'            => 'NL',
                            'location_code' => 'ed14eb91-7374-4dcc-a41d-34c0d3e45c01',
                        ],
                        'hidden'                       => 0,
                        'price'                        => [
                            'amount'   => 1920,
                            'currency' => 'EUR',
                        ],
                        'barcode'                      => '',
                        'region'                       => 'NL',
                        'external_provider'            => null,
                        'external_provider_id'         => null,
                        'payment_status'               => 'unpaid',
                        'carrier_id'                   => 5,
                        'contract_id'                  => null,
                        'platform_id'                  => 1,
                        'origin'                       => 'backoffice_shipment_form',
                        'user_agent'                   => 'ShipmentForm/',
                        'secondary_shipments'          => [],
                        'collection_contact'           => null,
                        'multi_collo_main_shipment_id' => null,
                        'external_identifier'          => null,
                        'delayed'                      => false,
                        'delivered'                    => false,
                        'link_consumer_portal'         => null,
                        'partner_tracktraces'          => [],
                    ],
                ],
            ],
        ];
    }
}

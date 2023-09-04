<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Contract\ClientResponseInterface;
use MyParcelNL\Pdk\Tests\Integration\Api\Response\BehatClientResponse;
use Symfony\Component\HttpFoundation\Request;

return [
    'match' => static function (Request $request): bool {
        return '/shipments/130400' === $request->getPathInfo()
            && 'GET' === $request->getMethod();
    },

    'response' => static function (Request $request): ClientResponseInterface {
        return new BehatClientResponse(
            json_encode([
                'data' => [
                    'shipments' => [
                        [
                            'id'                           => 130400,
                            'parent_id'                    => null,
                            'account_id'                   => 126594,
                            'shop_id'                      => 39399,
                            'shipment_type'                => 1,
                            'recipient'                    => [
                                'cc'          => 'NL',
                                'city'        => 'Hoofddorp',
                                'company'     => 'MyParcel',
                                'person'      => 'Felicia Parcel',
                                'postal_code' => '2132 JE',
                                'street'      => 'Antareslaan',
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
                                'package_type'      => 1,
                                'delivery_type'     => 2,
                                'insurance'         => [
                                    'amount'   => 0,
                                    'currency' => 'EUR',
                                ],
                                'label_description' => 'standaard kenmerk',
                            ],
                            'general_settings'             => [
                                'save_recipient_address' => 0,
                                'tracktrace'             => [
                                    'carrier_email_basic_notification' => 1,
                                    'send_track_trace_emails'          => 1,
                                    'email_on_handed_to_courier'       => 0,
                                    'bcc'                              => 0,
                                    'delivery_notification'            => 0,
                                    'bcc_email'                        => 'edie@myparcel.nl',
                                    'from_address_email'               => 'edie@myparcel.nl',
                                    'from_address_company'             => 'Je moeder',
                                ],
                            ],
                            'pickup'                       => null,
                            'customs_declaration'          => null,
                            'physical_properties'          => [
                                'weight' => 0,
                            ],
                            'created'                      => '2023-09-01 11:18:09',
                            'modified'                     => '2023-09-01 11:18:09',
                            'reference_identifier'         => '12',
                            'created_by'                   => 35159,
                            'modified_by'                  => 35159,
                            'transaction_status'           => 'unpaid',
                            'drop_off_point'               => null,
                            'hidden'                       => 0,
                            'price'                        => [
                                'amount'   => 637,
                                'currency' => 'EUR',
                            ],
                            'barcode'                      => '3SXDXU085250127',
                            'region'                       => 'NL',
                            'external_provider'            => null,
                            'external_provider_id'         => null,
                            'payment_status'               => 'unpaid',
                            'carrier_id'                   => 1,
                            'platform_id'                  => 1,
                            'origin'                       => null,
                            'user_agent'                   => null,
                            'secondary_shipments'          => [],
                            'collection_contact'           => null,
                            'multi_collo_main_shipment_id' => null,
                            'external_identifier'          => '3SXDXU085250127',
                            'delayed'                      => false,
                            'delivered'                    => false,
                            'contract_id'                  => 8940,
                            'link_consumer_portal'         => 'https://edie.myparcel.me/track-trace/3SXDXU085250127/2132%20JE/NL',
                            'partner_tracktraces'          => [],
                            'pickup_request_number'        => null,
                        ],
                    ],
                    'results'   => 1,
                ],
            ]), 200
        );
    },
];

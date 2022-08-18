<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\StreamInterface;

class GetShipmentsResponseWithPickup extends JsonResponse
{
    public function getBody(): StreamInterface
    {
        return Utils::streamFor(
            json_encode(
                [
                    'data' => [
                        'shipments' => [
                            [
                                'id'                           => 136464938,
                                'parent_id'                    => null,
                                'account_id'                   => 170402,
                                'shop_id'                      => 93683,
                                'shipment_type'                => 1,
                                'recipient'                    => [
                                    'cc'                     => 'NL',
                                    'postal_code'            => '2771AX',
                                    'city'                   => 'Boskoop',
                                    'street'                 => 'Azalealaan',
                                    'street_additional_info' => '',
                                    'number'                 => '25',
                                    'number_suffix'          => 'a',
                                    'person'                 => 'Piet Boomkweker',
                                    'email'                  => 'mooieplanten@kweker.nl',
                                    'phone'                  => '0613124565',
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
                                'status'                       => 2,
                                'options'                      => [
                                    'package_type'             => 1,
                                    'collect'                  => 0,
                                    'only_recipient'           => 0,
                                    'signature'                => 1,
                                    'return'                   => 0,
                                    'insurance'                => [
                                        'amount'   => 0,
                                        'currency' => 'EUR',
                                    ],
                                    'large_format'             => 0,
                                    'same_day_delivery'        => 0,
                                    'age_check'                => 0,
                                    'saturday_delivery'        => 0,
                                    'drop_off_at_postal_point' => 0,
                                    'label_description'        => '',
                                    'delivery_type'            => 4,
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
                                'pickup'                       => [
                                    'postal_code'       => '2771GS',
                                    'location_name'     => 'Wolswijk thodn Intertoys Boskoop',
                                    'city'              => 'BOSKOOP',
                                    'street'            => 'Kerkstraat',
                                    'number'            => '7',
                                    'cc'                => 'NL',
                                    'location_code'     => '216107',
                                    'retail_network_id' => 'PNPNL-01',
                                ],
                                'customs_declaration'          => null,
                                'physical_properties'          => [
                                    'weight' => 1000,
                                ],
                                'created'                      => '2022-07-12 17:28:27',
                                'modified'                     => '2022-07-12 17:28:28',
                                'reference_identifier'         => '',
                                'created_by'                   => 82444,
                                'modified_by'                  => 82444,
                                'transaction_status'           => 'unpaid',
                                'drop_off_point'               => null,
                                'hidden'                       => 0,
                                'price'                        => [
                                    'amount'   => 625,
                                    'currency' => 'EUR',
                                ],
                                'barcode'                      => '3SMYPA056396924',
                                'region'                       => 'NL',
                                'external_provider'            => null,
                                'external_provider_id'         => null,
                                'payment_status'               => 'unpaid',
                                'carrier_id'                   => 1,
                                'contract_id'                  => null,
                                'platform_id'                  => 1,
                                'origin'                       => 'backoffice_shipment_form',
                                'user_agent'                   => 'ShipmentForm/',
                                'secondary_shipments'          => [],
                                'collection_contact'           => null,
                                'multi_collo_main_shipment_id' => null,
                                'external_identifier'          => '3SMYPA056396924',
                                'delayed'                      => false,
                                'delivered'                    => false,
                                'link_consumer_portal'         => null,
                                'partner_tracktraces'          => [],
                            ],
                        ],
                    ],
                ]
            )
        );
    }
}

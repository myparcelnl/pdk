<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

class ExampleGetAccountsResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'id'                 => 120,
                'contact_id'         => 302,
                'platform_id'        => 1,
                'origin_id'          => 1,
                'status'             => 2,
                'terms_agreed'       => true,
                'username'           => 'felicia@myparcel.nl',
                'first_name'         => 'Felicia',
                'last_name'          => 'Parcel',
                'email'              => 'felicia@myparcel.nl',
                'phone'              => '06123456789',
                'general_settings'   => [
                    'is_test'                            => 1,
                    'order_mode'                         => 1,
                    'affiliate_bcc'                      => 1,
                    'affiliate_fee'                      => [
                        'amount'   => 10,
                        'currency' => 'EUR',
                    ],
                    'order_settings'                     => [
                        'shipment_label' => 'none',
                    ],
                    'show_cumulio_dashboard'             => 0,
                    'allow_printerless_return'           => 1,
                    'has_carrier_contract'               => 0,
                    'has_carrier_mail_contract'          => 0,
                    'has_carrier_small_package_contract' => 0,
                    'use_mfa'                            => 0,
                ],
                'additional_info'    => [
                    'ecommerce_platform' => '17',
                ],
                'shipment_estimates' => [
                    [
                        'region'       => 'NL',
                        'estimate'     => 1000,
                        'package_type' => 2,
                    ],
                    [
                        'region'       => 'NL',
                        'estimate'     => 500,
                        'package_type' => 1,
                    ],
                    [
                        'region'       => 'EU',
                        'estimate'     => 100,
                        'package_type' => 1,
                    ],
                    [
                        'region'       => 'CD',
                        'estimate'     => 50,
                        'package_type' => 1,
                    ],
                ],
                'delivery_address'   => [
                    'first_name' => 'Felicia',
                    'last_name'  => 'Parcel',
                ],
                'created'            => '2018-10-01 16:10:53',
                'modified'           => '2023-06-20 11:44:47',
                'carrier_references' => [],
                'contact'            => [
                    'id'                     => 302,
                    'first_name'             => 'Felicia',
                    'last_name'              => 'Parcel',
                    'email'                  => 'felicia@myparcel.nl',
                    'phone'                  => '06123456789',
                    'company'                => null,
                    'street'                 => '',
                    'number'                 => '',
                    'number_suffix'          => '',
                    'box_number'             => '',
                    'postal_code'            => '',
                    'city'                   => '',
                    'cc'                     => null,
                    'street_additional_info' => '',
                    'region'                 => '',
                ],
                'shops'              => [
                    [
                        'id'                     => 2100,
                        'account_id'             => 120,
                        'platform_id'            => 1,
                        'name'                   => 'PotlodenShop',
                        'hidden'                 => 0,
                        'billing'                => [
                            'address'                => [
                                'cc'                     => 'NL',
                                'postal_code'            => '2132 JE',
                                'city'                   => 'Hoofddorp',
                                'street'                 => 'Antareslaan',
                                'number'                 => '31',
                                'number_suffix'          => '',
                                'person'                 => 'Felicia Parcel',
                                'company'                => 'PotlodenShop',
                                'email'                  => 'felicia@myparcel.nl',
                                'phone'                  => '06123456789',
                                'street_additional_info' => '',
                                'billing_email'          => '',
                            ],
                            'billing_method'         => 2,
                            'vat_tariff'             => 210,
                            'vat_type'               => 'general',
                            'iban'                   => 'NL41INGB1234567890',
                            'vat_number'             => 'NL123456789B01',
                            'coc'                    => '',
                            'bic'                    => 'INGBNL2A',
                            'send_invoice_reminders' => 0,
                            'cycle'                  => 'monthly',
                            'reference'              => '',
                            'eori_number'            => '',
                            'vat_number_uk'          => '',
                        ],
                        'return'                 => [
                            'address'                                       => [
                                'cc'                     => 'NL',
                                'postal_code'            => '2132 JE',
                                'city'                   => 'Hoofddorp',
                                'street'                 => 'Antareslaan',
                                'number'                 => '31',
                                'number_suffix'          => '',
                                'person'                 => 'Felicia Parcel',
                                'company'                => 'PotlodenShop',
                                'email'                  => 'felicia@myparcel.nl',
                                'phone'                  => '06123456789',
                                'street_additional_info' => '',
                            ],
                            'link_expires_after'                            => 3650,
                            'use_shop_shipment_options'                     => 0,
                            'send_tracktrace_email_for_return_shipments'    => 0,
                            'use_custom_description'                        => 0,
                            'from_address_name'                             => 'PotlodenShop',
                            'bcc'                                           => 0,
                            'email_address_for_tracktrace_return_shipments' => null,
                            'contribution'                                  => [
                                'amount'   => 0,
                                'currency' => 'EUR',
                            ],
                            'use_printerless_return'                        => 0,
                            'settle_printerless_return_cost'                => 0,
                        ],
                        'delivery_address'       => [
                            'cc'                     => 'NL',
                            'postal_code'            => '2132 WT',
                            'city'                   => 'Hoofddorp',
                            'street'                 => 'Siriusdreef',
                            'number'                 => '66',
                            'number_suffix'          => '',
                            'company'                => 'PotlodenShop',
                            'email'                  => 'felicia@myparcel.nl',
                            'phone'                  => '06123456789',
                            'street_additional_info' => '',
                            'first_name'             => 'Felicia',
                            'last_name'              => 'Parcel',
                        ],
                        'tracktrace'             => [
                            'carrier_email_basic_notification' => 1,
                            'send_track_trace_emails'          => 1,
                            'email_on_handed_to_courier'       => 0,
                            'bcc'                              => 0,
                            'delivery_notification'            => 0,
                            'bcc_email'                        => 'felicia@myparcel.nl',
                            'from_address_email'               => 'felicia@myparcel.nl',
                            'from_address_company'             => 'Je moeder',
                        ],
                        'general_settings'       => [
                            'weight'                                    => 1000,
                            'label_format'                              => 'A6',
                            'postage_paid'                              => 0,
                            'reminder_email'                            => 0,
                            'use_logo_email'                            => 0,
                            'use_logo_label'                            => 1,
                            'preferred_locale'                          => 'nl-NL',
                            'label_description'                         => 'standaard kenmerk',
                            'auto_save_addresses'                       => 1,
                            'label_format_locked'                       => 1,
                            'total_shipments_on_overview'               => 10,
                            'has_seen_digital_stamp_print_notification' => 1,
                        ],
                        'shipment_options'       => [
                            'package_type'             => 1,
                            'only_recipient'           => 1,
                            'signature'                => 1,
                            'return'                   => 0,
                            'insurance'                => [
                                'amount'   => 100000,
                                'currency' => 'EUR',
                            ],
                            'large_format'             => 0,
                            'cooled_delivery'          => 0,
                            'label_description'        => 'standaard kenmerk',
                            'age_check'                => 0,
                            'saturday_delivery'        => 0,
                            'drop_off_at_postal_point' => 0,
                            'collect'                  => 0,
                            'same_day_delivery'        => 0,
                            'printerless_return'       => 0,
                            'hide_sender'              => 0,
                        ],
                        'created'                => '2018-10-01 16:10:54',
                        'modified'               => '2023-06-20 11:20:44',
                        'branding'               => [
                            'id'                                        => 3,
                            'shop_id'                                   => 2100,
                            'accent_color'                              => '#ef6e4e',
                            'subdomain'                                 => 'felicia',
                            'created'                                   => '2019-01-22 08:08:46',
                            'modified'                                  => '2020-09-08 14:05:02',
                            'enable_track_trace'                        => true,
                            'enable_returns'                            => true,
                            'allow_creating_related_returns'            => true,
                            'related_returns_allowed_period'            => 28,
                            'related_returns_use_return_contributions'  => false,
                            'related_returns_use_shop_shipment_options' => false,
                            'use_consumer_portal'                       => true,
                            'banner'                                    => 'path/to/banner.jpg',
                            'logo'                                      => 'logos/120/2100/logo_email.png',
                            'label_logo'                                => 'logos/120/2100/logo_label.png',
                        ],
                        'return_reason_settings' => [
                            'enabled'        => true,
                            'mandatory'      => true,
                            'return_reasons' => [
                                [
                                    'name'  => 'changed_my_mind',
                                    'human' => 'Ik heb me bedacht',
                                ],
                                [
                                    'name'  => 'dont_need_it_anymore',
                                    'human' => 'Ik heb het artikel niet meer nodig',
                                ],
                                [
                                    'name'  => 'ordered_wrong_product',
                                    'human' => 'Ik heb het verkeerde product besteld',
                                ],
                                [
                                    'name'  => 'product_not_as_expected',
                                    'human' => 'Het artikel komt niet overeen met afbeelding/productinformatie',
                                ],
                                [
                                    'name'  => 'other',
                                    'human' => 'Andere reden',
                                ],
                                [
                                    'name'  => 'product_not_functional',
                                    'human' => 'Het artikel werkt niet',
                                ],
                                [
                                    'name'  => 'product_too_large',
                                    'human' => 'Het artikel is te groot',
                                ],
                            ],
                        ],
                    ],
                ],
                'users'              => [
                    [
                        'id'              => 10000,
                        'account_id'      => 120,
                        'username'        => 'felicia@myparcel.nl',
                        'status'          => 'active',
                        'created'         => '2018-10-01 16:10:53',
                        'all_shop_access' => true,
                        'contact'         => [
                            'id'                     => 302,
                            'first_name'             => 'Felicia',
                            'last_name'              => 'Parcel',
                            'email'                  => 'felicia@myparcel.nl',
                            'phone'                  => '06123456789',
                            'company'                => null,
                            'street'                 => '',
                            'number'                 => '',
                            'number_suffix'          => '',
                            'box_number'             => '',
                            'postal_code'            => '',
                            'city'                   => '',
                            'cc'                     => null,
                            'street_additional_info' => '',
                            'region'                 => '',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return string
     */
    protected function getResponseProperty(): string
    {
        return 'accounts';
    }
}

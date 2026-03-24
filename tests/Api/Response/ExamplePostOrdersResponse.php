<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Api\Response;

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrier;

class ExamplePostOrdersResponse extends ExampleJsonResponse
{
    /**
     * @return array[]
     */
    protected function getDefaultResponseContent(): array
    {
        return [
            [
                'invoice_address' => null,
                'language'       => null,
                'order_date'      => '2022-08-22 00:00:00',
                'order_lines'          => [
                    [
                        'uuid'          => '1234',
                        'quantity'      => 1,
                        'price'         => 250,
                        'vat'           => 10,
                        'price_after_vat' => 260,
                        'product'       => [
                            'uuid'               => '12345',
                            'sku'                => '018234',
                            'ean'                => '018234',
                            'external_identifier' => '018234',
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
                    'contract_id'        => '123',
                    'carrier'            => RefTypesCarrier::POSTNL,
                    'customs_declaration' => [
                        'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                        'invoice'  => '25',
                        'items'    => [
                            [
                                'amount'         => 1,
                                'classification' => 5256,
                                'country'        => CountryCodes::CC_BE,
                                'description'    => 'Vlaamse Patatekes',
                                'item_value'      => ['amount' => 5000, 'currency' => 'EUR'],
                                'weight'         => 200,
                            ],
                            [
                                'amount'         => 1,
                                'classification' => 9221,
                                'country'        => CountryCodes::CC_FR,
                                'description'    => 'Omelette du Fromage',
                                'item_value'      => ['amount' => 10000, 'currency' => 'EUR'],
                                'weight'         => 350,
                            ],
                        ],
                    ],
                    'drop_off_point'       => null,
                    'physical_properties' => [
                        'weight' => 3500,
                    ],
                    'recipient'          => [
                        'cc'         => 'NL',
                        'city'       => 'Hoofddorp',
                        'person'     => 'Jaap Krekel',
                        'postal_code' => '2132JE',
                        'address1'   => 'Antareslaan 31',
                    ],
                    'sender'             => [
                        'cc'         => 'NL',
                        'city'       => 'Amsterdam',
                        'person'     => 'Willem Wever',
                        'postal_code' => '4164ZF',
                        'address1'   => 'Werf 2',
                    ],
                ],
                'shop_id'         => null,
                'status'         => null,
                'type'           => null,
                'updated_at'     => null,
                'uuid'           => '123',
                'vat'            => null,
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

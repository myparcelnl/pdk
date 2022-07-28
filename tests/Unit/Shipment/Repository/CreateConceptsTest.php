<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Data\CountryCodes;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\ShipmentRepository;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Api\Response\PostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Facade\MockApi;
use MyParcelNL\Sdk\src\Support\Arr;

const DEFAULT_OUTPUT_RECIPIENT = [
    'recipient.cc'          => 'NL',
    'recipient.city'        => 'Hoofddorp',
    'recipient.person'      => 'Jaappie Krekel',
    'recipient.postal_code' => '2132JE',
    'recipient.street'      => 'Antareslaan 31',
];

const DEFAULT_INPUT_RECIPIENT = [
    'cc'         => 'NL',
    'city'       => 'Hoofddorp',
    'person'     => 'Jaappie Krekel',
    'postalCode' => '2132JE',
    'street'     => 'Antareslaan 31',
];

const DEFAULT_INPUT_SENDER = [
    'cc'         => 'NL',
    'city'       => 'Amsterdam',
    'number'     => '2',
    'person'     => 'Willem Wever',
    'postalCode' => '4164ZF',
    'street'     => 'Werf',
];

it('creates a valid request from a shipment collection', function (array $input, array $output) {
    PdkFactory::create(MockPdkConfig::DEFAULT_CONFIG);

    $mock = MockApi::getMock();
    $mock->append(new PostShipmentsResponse());

    $inputShipments  = (new Collection($input))->mapInto(Shipment::class);
    $createdConcepts = ShipmentRepository::createConcepts(
        new ShipmentCollection($inputShipments->all())
    );

    $request = $mock->getLastRequest();
    $body    = json_decode(
        $request->getBody()
            ->getContents(),
        true
    );

    $shipments = Arr::get($body, 'data.shipments');

    expect($shipments)
        ->toBeArray()
        ->and($createdConcepts)
        ->toBeInstanceOf(ShipmentCollection::class)
        ->and(
            array_map(function (array $shipment) {
                return Arr::dot($shipment);
            }, $shipments)
        )
        ->toEqual($output);
})->with([
    'bare minimum'                                => [
        'input'  => [
            [
                'carrier'   => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'recipient' => DEFAULT_INPUT_RECIPIENT,
            ],
        ],
        'output' => [
            array_merge(DEFAULT_OUTPUT_RECIPIENT, [
                'carrier'              => CarrierOptions::CARRIER_POSTNL_ID,
                'options.package_type' => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
            ]),
        ],
    ],
    'simple domestic shipment'                    => [
        'input'  => [
            [
                'carrier'            => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'deliveryOptions'    => [
                    'date'            => '2022-07-10 16:00:00',
                    'shipmentOptions' => [
                        'ageCheck'         => true,
                        'insurance'        => 500,
                        'labelDescription' => 'order 204829',
                        'largeFormat'      => false,
                        'onlyRecipient'    => true,
                        'return'           => false,
                        'sameDayDelivery'  => false,
                        'signature'        => false,
                    ],
                ],
                'physicalProperties' => [
                    'height' => 100,
                    'width'  => 120,
                    'length' => 80,
                    'weight' => 2000,
                ],
                'recipient'          => DEFAULT_INPUT_RECIPIENT,
                'sender'             => DEFAULT_INPUT_SENDER,
            ],
        ],
        'output' => [
            array_merge(
                DEFAULT_OUTPUT_RECIPIENT,
                [
                    'carrier'                    => CarrierOptions::CARRIER_POSTNL_ID,
                    'options.age_check'          => 1,
                    'options.delivery_date'      => '2022-07-10 16:00:00',
                    'options.insurance.amount'   => 50000,
                    'options.insurance.currency' => 'EUR',
                    'options.label_description'  => 'order 204829',
                    'options.only_recipient'     => 1,
                    'options.package_type'       => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                    'physical_properties.weight' => 2000,
                ]
            ),
        ],
    ],
    'domestic with pickup'                        => [
        'input'  => [
            [
                'carrier'         => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'recipient'       => DEFAULT_INPUT_RECIPIENT,
                'deliveryOptions' => [
                    'deliveryType'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                    'pickupLocation' => [
                        'locationCode' => 12345,
                    ],
                ],
            ],
        ],
        'output' => [
            array_merge(
                DEFAULT_OUTPUT_RECIPIENT,
                [
                    'carrier'               => CarrierOptions::CARRIER_POSTNL_ID,
                    'options.package_type'  => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                    'options.delivery_type' => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                    'pickup.location_code'  => 12345,
                ]
            ),
        ],
    ],
    'instabox same day delivery'                  => [
        'input'  => [
            [
                'carrier'         => ['name' => CarrierOptions::CARRIER_INSTABOX_NAME],
                'recipient'       => DEFAULT_INPUT_RECIPIENT,
                'deliveryOptions' => [
                    'shipmentOptions' => [
                        'sameDayDelivery' => true,
                    ],
                ],
                'dropOffPoint'    => [
                    'locationCode' => 45678,
                ],
            ],
        ],
        'output' => [
            array_merge(
                DEFAULT_OUTPUT_RECIPIENT,
                [
                    'carrier'                      => CarrierOptions::CARRIER_INSTABOX_ID,
                    'options.package_type'         => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                    'options.same_day_delivery'    => 1,
                    'drop_off_point.location_code' => 45678,
                    'drop_off_point.postal_code'   => '',
                    'drop_off_point.location_name' => '',
                    'drop_off_point.city'          => '',
                    'drop_off_point.street'        => '',
                    'drop_off_point.number'        => '',
                ]
            ),
        ],
    ],
    'eu shipment'                                 => [
        'input'  => [
            [
                'carrier'            => ['id' => CarrierOptions::CARRIER_BPOST_ID],
                'recipient'          => ['cc' => CountryCodes::CC_CA] + DEFAULT_INPUT_RECIPIENT,
                'customsDeclaration' => [
                    'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                    'invoice'  => '25',
                    'items'    => [
                        [
                            'amount'         => 1,
                            'classification' => 9609,
                            'country'        => CountryCodes::CC_NL,
                            'description'    => 'trendy pencil',
                            'itemValue'      => ['amount' => 5000, 'currency' => 'EUR'],
                            'weight'         => 200,
                        ],
                        [
                            'amount'         => 1,
                            'classification' => 40169200,
                            'country'        => CountryCodes::CC_NL,
                            'description'    => 'beautiful eraser',
                            'itemValue'      => ['amount' => 10000, 'currency' => 'EUR'],
                            'weight'         => 350,
                        ],
                    ],
                ],
            ],
        ],
        'output' => [
            array_merge(DEFAULT_OUTPUT_RECIPIENT, [
                'carrier'                                        => CarrierOptions::CARRIER_BPOST_ID,
                'recipient.cc'                                   => CountryCodes::CC_CA,
                'options.package_type'                           => 1,
                'customs_declaration.contents'                   => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                'customs_declaration.invoice'                    => '25',
                'customs_declaration.items.0.amount'             => 1,
                'customs_declaration.items.0.classification'     => '9609',
                'customs_declaration.items.0.country'            => 'NL',
                'customs_declaration.items.0.description'        => 'trendy pencil',
                'customs_declaration.items.0.itemValue.amount'   => 5000,
                'customs_declaration.items.0.itemValue.currency' => 'EUR',
                'customs_declaration.items.0.weight'             => 200,
                'customs_declaration.items.1.amount'             => 1,
                'customs_declaration.items.1.classification'     => '40169200',
                'customs_declaration.items.1.country'            => 'NL',
                'customs_declaration.items.1.description'        => 'beautiful eraser',
                'customs_declaration.items.1.itemValue.amount'   => 10000,
                'customs_declaration.items.1.itemValue.currency' => 'EUR',
                'customs_declaration.items.1.weight'             => 350,
                'customs_declaration.weight'                     => 550,
            ]),
        ],
    ],
    'shipment with weight in customs declaration' => [
        'input'  => [
            [
                'carrier'            => ['id' => CarrierOptions::CARRIER_BPOST_ID],
                'recipient'          => ['cc' => CountryCodes::CC_DE] + DEFAULT_INPUT_RECIPIENT,
                'deliveryOptions'    => [
                    'deliveryType'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                    'pickupLocation' => [
                        'locationCode' => 34653,
                    ],
                ],
                'physicalProperties' => [
                    'weight' => 0,
                ],
                'customsDeclaration' => [
                    'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                    'invoice'  => '14',
                    'items'    => [
                        [
                            'amount'         => 1,
                            'classification' => 9609,
                            'country'        => CountryCodes::CC_BE,
                            'description'    => 'stofzuiger',
                            'itemValue'      => ['amount' => 5000, 'currency' => 'EUR'],
                            'weight'         => 200,
                        ],
                        [
                            'amount'         => 2,
                            'classification' => 420690,
                            'country'        => CountryCodes::CC_NL,
                            'description'    => 'ruler',
                            'itemValue'      => ['amount' => 900, 'currency' => 'EUR'],
                            'weight'         => 120,
                        ],
                    ],
                ],
            ],
        ],
        'output' => [
            array_merge(DEFAULT_OUTPUT_RECIPIENT, [
                'carrier'                                        => CarrierOptions::CARRIER_BPOST_ID,
                'recipient.cc'                                   => CountryCodes::CC_DE,
                'pickup.location_code'                           => '34653',
                'options.delivery_type'                          => 4,
                'options.package_type'                           => 1,
                'customs_declaration.contents'                   => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                'customs_declaration.invoice'                    => '14',
                'customs_declaration.items.0.amount'             => 1,
                'customs_declaration.items.0.classification'     => '9609',
                'customs_declaration.items.0.country'            => 'BE',
                'customs_declaration.items.0.description'        => 'stofzuiger',
                'customs_declaration.items.0.itemValue.amount'   => 5000,
                'customs_declaration.items.0.itemValue.currency' => 'EUR',
                'customs_declaration.items.0.weight'             => 200,
                'customs_declaration.items.1.amount'             => 2,
                'customs_declaration.items.1.classification'     => '420690',
                'customs_declaration.items.1.country'            => 'NL',
                'customs_declaration.items.1.description'        => 'ruler',
                'customs_declaration.items.1.itemValue.amount'   => 900,
                'customs_declaration.items.1.itemValue.currency' => 'EUR',
                'customs_declaration.items.1.weight'             => 120,
                'customs_declaration.weight'                     => 440,
                'physical_properties.weight'                     => 440,
            ]),
        ],
    ],
    'eu shipment with pickup'                     => [
        'input'  => [
            [
                'carrier'            => ['id' => CarrierOptions::CARRIER_BPOST_ID],
                'recipient'          => ['cc' => CountryCodes::CC_DE] + DEFAULT_INPUT_RECIPIENT,
                'deliveryOptions'    => [
                    'deliveryType'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                    'pickupLocation' => [
                        'locationCode' => 34653,
                    ],
                ],
                'customsDeclaration' => [
                    'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                    'invoice'  => '14',
                    'items'    => [
                        [
                            'amount'         => 1,
                            'classification' => 9609,
                            'country'        => CountryCodes::CC_BE,
                            'description'    => 'stofzuiger',
                            'itemValue'      => ['amount' => 5000, 'currency' => 'EUR'],
                            'weight'         => 200,
                        ],
                        [
                            'amount'         => 2,
                            'classification' => 420690,
                            'country'        => CountryCodes::CC_NL,
                            'description'    => 'ruler',
                            'itemValue'      => ['amount' => 900, 'currency' => 'EUR'],
                            'weight'         => 120,
                        ],
                    ],
                ],
            ],
        ],
        'output' => [
            array_merge(DEFAULT_OUTPUT_RECIPIENT, [
                'carrier'                                        => CarrierOptions::CARRIER_BPOST_ID,
                'recipient.cc'                                   => CountryCodes::CC_DE,
                'pickup.location_code'                           => '34653',
                'options.delivery_type'                          => 4,
                'options.package_type'                           => 1,
                'customs_declaration.contents'                   => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                'customs_declaration.invoice'                    => '14',
                'customs_declaration.items.0.amount'             => 1,
                'customs_declaration.items.0.classification'     => '9609',
                'customs_declaration.items.0.country'            => 'BE',
                'customs_declaration.items.0.description'        => 'stofzuiger',
                'customs_declaration.items.0.itemValue.amount'   => 5000,
                'customs_declaration.items.0.itemValue.currency' => 'EUR',
                'customs_declaration.items.0.weight'             => 200,
                'customs_declaration.items.1.amount'             => 2,
                'customs_declaration.items.1.classification'     => '420690',
                'customs_declaration.items.1.country'            => 'NL',
                'customs_declaration.items.1.description'        => 'ruler',
                'customs_declaration.items.1.itemValue.amount'   => 900,
                'customs_declaration.items.1.itemValue.currency' => 'EUR',
                'customs_declaration.items.1.weight'             => 120,
                'customs_declaration.weight'                     => 440,
            ]),
        ],
    ],
    'multicollo'                                  => [
        'input'  => [
            [
                'carrier'             => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'multiCollo'          => true,
                'recipient'           => DEFAULT_INPUT_RECIPIENT,
                'referenceIdentifier' => 'my-multicollo-set',
            ],
            [
                'carrier'             => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'multiCollo'          => true,
                'recipient'           => DEFAULT_INPUT_RECIPIENT,
                'referenceIdentifier' => 'my-multicollo-set',
            ],
        ],
        'output' => [
            array_merge(DEFAULT_OUTPUT_RECIPIENT, [
                'carrier'                                    => CarrierOptions::CARRIER_POSTNL_ID,
                'options.package_type'                       => 1,
                'reference_identifier'                       => 'my-multicollo-set',
                'secondary_shipments.0.reference_identifier' => 'my-multicollo-set',
            ]),
        ],
    ],
    'multiple shipments'                          => [
        'input'  => [
            [
                'carrier'            => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'deliveryOptions'    => [
                    'date'            => '2022-07-20 16:00:00',
                    'shipmentOptions' => [
                        'ageCheck'         => true,
                        'insurance'        => 0,
                        'labelDescription' => 'order 204829',
                        'largeFormat'      => false,
                        'onlyRecipient'    => true,
                        'return'           => false,
                        'sameDayDelivery'  => false,
                        'signature'        => false,
                    ],
                ],
                'physicalProperties' => [
                    'height' => 100,
                    'width'  => 120,
                    'length' => 80,
                    'weight' => 2000,
                ],
                'recipient'          => DEFAULT_INPUT_RECIPIENT,
                'sender'             => DEFAULT_INPUT_SENDER,
            ],
            [
                'carrier'            => ['id' => CarrierOptions::CARRIER_INSTABOX_ID],
                'deliveryOptions'    => [
                    'date'            => '2022-07-20 16:00:00',
                    'shipmentOptions' => [
                        'ageCheck'         => true,
                        'insurance'        => 500,
                        'labelDescription' => 'order 204829',
                        'largeFormat'      => false,
                        'onlyRecipient'    => true,
                        'return'           => false,
                        'sameDayDelivery'  => false,
                        'signature'        => false,
                    ],
                ],
                'physicalProperties' => [
                    'height' => 100,
                    'width'  => 120,
                    'length' => 80,
                    'weight' => 2000,
                ],
                'recipient'          => DEFAULT_INPUT_RECIPIENT,
                'sender'             => DEFAULT_INPUT_SENDER,
            ],
        ],
        'output' => [
            array_merge(DEFAULT_OUTPUT_RECIPIENT, [
                'carrier'                    => CarrierOptions::CARRIER_POSTNL_ID,
                'options.package_type'       => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                'options.delivery_date'      => '2022-07-20 16:00:00',
                'options.age_check'          => 1,
                'options.label_description'  => 'order 204829',
                'options.only_recipient'     => 1,
                'physical_properties.weight' => 2000,
            ]),
            array_merge(DEFAULT_OUTPUT_RECIPIENT, [
                'carrier'                    => CarrierOptions::CARRIER_INSTABOX_ID,
                'options.package_type'       => DeliveryOptions::PACKAGE_TYPE_PACKAGE_ID,
                'options.delivery_date'      => '2022-07-20 16:00:00',
                'options.age_check'          => 1,
                'options.insurance.amount'   => 50000,
                'options.insurance.currency' => 'EUR',
                'options.label_description'  => 'order 204829',
                'options.only_recipient'     => 1,
                'physical_properties.weight' => 2000,
            ]),
        ],
    ],
]);

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Service\CountryService;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;

const DEFAULT_OUTPUT_RECIPIENT = [
    'recipient.cc'          => 'NL',
    'recipient.city'        => 'Hoofddorp',
    'recipient.person'      => 'Jaap Krekel',
    'recipient.postal_code' => '2132JE',
    'recipient.street'      => 'Antareslaan 31',
];

const DEFAULT_INPUT_RECIPIENT = [
    'cc'         => 'NL',
    'city'       => 'Hoofddorp',
    'person'     => 'Jaap Krekel',
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
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostShipmentsResponse());

    $mock->append(
        new ExamplePostShipmentsResponse(
            array_map(function (array $data) {
                return [
                    'id'                   => mt_rand(),
                    'reference_identifier' => $data['reference_identifier'],
                ];
            }, $input)
        )
    );

    $inputShipments = (new ShipmentCollection($input));

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = $pdk->get(ShipmentRepository::class);

    $createdConcepts = $repository->createConcepts($inputShipments);

    $request = $mock->getLastRequest();

    if (! $request) {
        throw new RuntimeException('Request not found.');
    }

    $body = json_decode(
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
                'carrier'               => CarrierOptions::CARRIER_POSTNL_ID,
                'options.delivery_type' => DeliveryOptions::DEFAULT_DELIVERY_TYPE_ID,
                'options.package_type'  => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
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
                    'options.delivery_type'      => DeliveryOptions::DEFAULT_DELIVERY_TYPE_ID,
                    'options.insurance.amount'   => 50000,
                    'options.insurance.currency' => 'EUR',
                    'options.label_description'  => 'order 204829',
                    'options.only_recipient'     => 1,
                    'options.package_type'       => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
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
                    'options.delivery_type' => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                    'options.package_type'  => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
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
                    'drop_off_point.city'          => '',
                    'drop_off_point.location_code' => 45678,
                    'drop_off_point.location_name' => '',
                    'drop_off_point.number'        => '',
                    'drop_off_point.postal_code'   => '',
                    'drop_off_point.street'        => '',
                    'options.package_type'         => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
                    'options.delivery_type'        => DeliveryOptions::DEFAULT_DELIVERY_TYPE_ID,
                    'options.same_day_delivery'    => 1,
                ]
            ),
        ],
    ],
    'eu shipment'                                 => [
        'input'  => [
            [
                'carrier'            => ['id' => CarrierOptions::CARRIER_BPOST_ID],
                'recipient'          => ['cc' => CountryService::CC_CA] + DEFAULT_INPUT_RECIPIENT,
                'customsDeclaration' => [
                    'contents' => CustomsDeclaration::CONTENTS_COMMERCIAL_GOODS,
                    'invoice'  => '25',
                    'items'    => [
                        [
                            'amount'         => 1,
                            'classification' => 9609,
                            'country'        => CountryService::CC_NL,
                            'description'    => 'trendy pencil',
                            'itemValue'      => ['amount' => 5000, 'currency' => 'EUR'],
                            'weight'         => 200,
                        ],
                        [
                            'amount'         => 1,
                            'classification' => 40169200,
                            'country'        => CountryService::CC_NL,
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
                'options.delivery_type'                          => DeliveryOptions::DEFAULT_DELIVERY_TYPE_ID,
                'options.package_type'                           => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
                'recipient.cc'                                   => CountryService::CC_CA,
            ]),
        ],
    ],
    'shipment with weight in customs declaration' => [
        'input'  => [
            [
                'carrier'            => ['id' => CarrierOptions::CARRIER_BPOST_ID],
                'recipient'          => ['cc' => CountryService::CC_DE] + DEFAULT_INPUT_RECIPIENT,
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
                            'country'        => CountryService::CC_BE,
                            'description'    => 'stofzuiger',
                            'itemValue'      => ['amount' => 5000, 'currency' => 'EUR'],
                            'weight'         => 200,
                        ],
                        [
                            'amount'         => 2,
                            'classification' => 420690,
                            'country'        => CountryService::CC_NL,
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
                'options.delivery_type'                          => DeliveryOptions::DELIVERY_TYPE_PICKUP_ID,
                'options.package_type'                           => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
                'physical_properties.weight'                     => 440,
                'pickup.location_code'                           => '34653',
                'recipient.cc'                                   => CountryService::CC_DE,
            ]),
        ],
    ],
    'eu shipment with pickup'                     => [
        'input'  => [
            [
                'carrier'            => ['id' => CarrierOptions::CARRIER_BPOST_ID],
                'recipient'          => ['cc' => CountryService::CC_DE] + DEFAULT_INPUT_RECIPIENT,
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
                            'country'        => CountryService::CC_BE,
                            'description'    => 'stofzuiger',
                            'itemValue'      => ['amount' => 5000, 'currency' => 'EUR'],
                            'weight'         => 200,
                        ],
                        [
                            'amount'         => 2,
                            'classification' => 420690,
                            'country'        => CountryService::CC_NL,
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
                'options.delivery_type'                          => 4,
                'options.package_type'                           => 1,
                'pickup.location_code'                           => '34653',
                'recipient.cc'                                   => CountryService::CC_DE,
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
                'options.delivery_type'                      => DeliveryOptions::DEFAULT_DELIVERY_TYPE_ID,
                'options.package_type'                       => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
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
                'options.delivery_type'      => DeliveryOptions::DEFAULT_DELIVERY_TYPE_ID,
                'options.package_type'       => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
                'options.delivery_date'      => '2022-07-20 16:00:00',
                'options.age_check'          => 1,
                'options.label_description'  => 'order 204829',
                'options.only_recipient'     => 1,
                'physical_properties.weight' => 2000,
            ]),
            array_merge(DEFAULT_OUTPUT_RECIPIENT, [
                'carrier'                    => CarrierOptions::CARRIER_INSTABOX_ID,
                'options.delivery_type'      => DeliveryOptions::DEFAULT_DELIVERY_TYPE_ID,
                'options.package_type'       => DeliveryOptions::DEFAULT_PACKAGE_TYPE_ID,
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

it('creates shipment', function ($input, $path, $query, $contentType) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostShipmentsResponse());

    $repository = $pdk->get(ShipmentRepository::class);

    $response = $repository->createConcepts(new ShipmentCollection($input));
    $request  = $mock->getLastRequest();

    if (! $request) {
        throw new RuntimeException('Request is not set');
    }

    $uri               = $request->getUri();
    $contentTypeHeader = Arr::first($request->getHeaders()['Content-Type']);

    expect($uri->getQuery())
        ->toBe($query)
        ->and($uri->getPath())
        ->toBe($path)
        ->and($contentTypeHeader)
        ->toBe($contentType)
        ->and($response)
        ->toBeInstanceOf(ShipmentCollection::class);
})->with([
    'single shipment' => [
        'input'       => [
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
        'path'        => 'API/shipments',
        'query'       => '',
        'contentType' => 'application/vnd.shipment+json;charset=utf-8;version=1.1',
    ],
]);

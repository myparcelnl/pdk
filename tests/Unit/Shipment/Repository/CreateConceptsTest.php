<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Repository;

use MyParcelNL\Pdk\Base\Facade\MockApi;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Mock\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

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

it('creates a valid request from a shipment collection', function (array $input) {
    MockApi::enqueue(
        new ExamplePostIdsResponse(),
        new ExamplePostIdsResponse(
            array_map(function (array $data) {
                return [
                    'id'                   => mt_rand(),
                    'reference_identifier' => $data['reference_identifier'],
                ];
            }, $input)
        )
    );

    $inputShipments = new ShipmentCollection($input);

    /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
    $repository = Pdk::get(ShipmentRepository::class);

    $createdConcepts = $repository->createConcepts($inputShipments);
    $request         = MockApi::ensureLastRequest();

    $body = json_decode(
        $request->getBody()
            ->getContents(),
        true
    );

    $shipments = Arr::get($body, 'data.shipments');

    expect($shipments)
        ->toBeArray()
        ->and($createdConcepts)
        ->toBeInstanceOf(ShipmentCollection::class);

    assertMatchesJsonSnapshot(json_encode($shipments));
})->with([
    'bare minimum'                                => [
        'input' => [
            [
                'carrier'   => ['id' => Carrier::CARRIER_POSTNL_ID],
                'recipient' => DEFAULT_INPUT_RECIPIENT,
            ],
        ],
    ],
    'simple domestic shipment'                    => [
        'input' => [
            [
                'carrier'            => ['id' => Carrier::CARRIER_POSTNL_ID],
                'deliveryOptions'    => [
                    'date'            => '2038-07-10 16:00:00',
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
    ],
    'shipment to be delivered in the past'        => [
        'input' => [
            [
                'carrier'         => ['id' => Carrier::CARRIER_POSTNL_ID],
                'deliveryOptions' => [
                    'date' => '2000-07-10 16:00:00',
                ],
                'recipient'       => DEFAULT_INPUT_RECIPIENT,
                'sender'          => DEFAULT_INPUT_SENDER,
            ],
        ],
    ],
    'domestic with pickup'                        => [
        'input' => [
            [
                'carrier'         => ['id' => Carrier::CARRIER_POSTNL_ID],
                'recipient'       => DEFAULT_INPUT_RECIPIENT,
                'deliveryOptions' => [
                    'deliveryType'   => DeliveryOptions::DELIVERY_TYPE_PICKUP_NAME,
                    'pickupLocation' => [
                        'locationCode' => 12345,
                    ],
                ],
            ],
        ],
    ],
    'instabox same day delivery'                  => [
        'input' => [
            [
                'carrier'         => ['name' => Carrier::CARRIER_INSTABOX_NAME],
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
    ],
    'eu shipment'                                 => [
        'input' => [
            [
                'carrier'            => ['id' => Carrier::CARRIER_BPOST_ID],
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
    ],
    'shipment with weight in customs declaration' => [
        'input' => [
            [
                'carrier'            => ['id' => Carrier::CARRIER_BPOST_ID],
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
    ],
    'eu shipment with pickup'                     => [
        'input' => [
            [
                'carrier'            => ['id' => Carrier::CARRIER_BPOST_ID],
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
    ],
    'multicollo'                                  => [
        'input' => [
            [
                'carrier'             => ['id' => Carrier::CARRIER_POSTNL_ID],
                'multiCollo'          => true,
                'recipient'           => DEFAULT_INPUT_RECIPIENT,
                'referenceIdentifier' => 'my-multicollo-set',
            ],
            [
                'carrier'             => ['id' => Carrier::CARRIER_POSTNL_ID],
                'multiCollo'          => true,
                'recipient'           => DEFAULT_INPUT_RECIPIENT,
                'referenceIdentifier' => 'my-multicollo-set',
            ],
        ],
    ],
    'multiple shipments'                          => [
        'input' => [
            [
                'carrier'            => ['id' => Carrier::CARRIER_POSTNL_ID],
                'deliveryOptions'    => [
                    'date'            => '2038-07-20 16:00:00',
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
                'carrier'            => ['id' => Carrier::CARRIER_INSTABOX_ID],
                'deliveryOptions'    => [
                    'date'            => '2038-07-20 16:00:00',
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
    ],
]);

it('creates shipment', function ($input, $path, $query, $contentType) {
    MockApi::enqueue(new ExamplePostIdsResponse());

    $repository = Pdk::get(ShipmentRepository::class);

    $response = $repository->createConcepts(new ShipmentCollection($input));
    $request  = MockApi::ensureLastRequest();

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
                'carrier'            => ['id' => Carrier::CARRIER_POSTNL_ID],
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

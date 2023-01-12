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
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Sdk\src\Support\Arr;
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
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostIdsResponse());

    $mock->append(
        new ExamplePostIdsResponse(
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
        ->toBeInstanceOf(ShipmentCollection::class);

    assertMatchesJsonSnapshot(json_encode($shipments));
})->with([
    'bare minimum'                                => [
        'input' => [
            [
                'carrier'   => ['id' => CarrierOptions::CARRIER_POSTNL_ID],
                'recipient' => DEFAULT_INPUT_RECIPIENT,
            ],
        ],
    ],
    'simple domestic shipment'                    => [
        'input' => [
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
    ],
    'domestic with pickup'                        => [
        'input' => [
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
    ],
    'instabox same day delivery'                  => [
        'input' => [
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
    ],
    'eu shipment'                                 => [
        'input' => [
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
    ],
    'shipment with weight in customs declaration' => [
        'input' => [
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
    ],
    'eu shipment with pickup'                     => [
        'input' => [
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
    ],
    'multicollo'                                  => [
        'input' => [
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
    ],
    'multiple shipments'                          => [
        'input' => [
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
    ],
]);

it('creates shipment', function ($input, $path, $query, $contentType) {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockApiService $api */
    $api  = $pdk->get(ApiServiceInterface::class);
    $mock = $api->getMock();
    $mock->append(new ExamplePostIdsResponse());

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

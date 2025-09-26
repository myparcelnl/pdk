<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Repository;

use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollectionFactory;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostIdsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

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

it(
    'creates a valid request from a shipment collection',
    function (ShipmentCollectionFactory $shipmentCollectionFactory) {
        $shipmentCollection = $shipmentCollectionFactory->make();
        $mockIdsCollection  = new Collection($shipmentCollection->all());

        MockApi::enqueue(
            new ExamplePostIdsResponse(),
            new ExamplePostIdsResponse(
                $mockIdsCollection->map(function ($shipment) {
                    return [
                        'id'                   => mt_rand(),
                        'reference_identifier' => $shipment->referenceIdentifier,
                    ];
                })
                    ->toArray()
            )
        );

        /** @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository $repository */
        $repository = Pdk::get(ShipmentRepository::class);

        $createdConcepts = $repository->createConcepts($shipmentCollection);
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
    }
)->with([
    'bare minimum'                                => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withRecipient(DEFAULT_INPUT_RECIPIENT)
            );
        },
    ],
    'subscription carrier'                        => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withContractId(1234)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withRecipient(DEFAULT_INPUT_RECIPIENT)
            );
        },
    ],
    'address with address1 and address2 combined' => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withRecipient(
                        array_merge(DEFAULT_INPUT_RECIPIENT, [
                            'address1' => 'Tuinstraat',
                            'address2' => '35',
                        ])
                    )
            );
        },
    ],

    'simple domestic shipment'                    => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withRecipient(
                        array_merge(DEFAULT_INPUT_RECIPIENT, [
                            'address1' => 'Tuinstraat',
                            'address2' => '35',
                        ])
                    )
            );
        },
    ],
    'shipment to be delivered in the past'        => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withDeliveryOptions(
                        factory(DeliveryOptions::class)->withDate('2000-07-10 16:00:00')
                    )
                    ->withRecipient(DEFAULT_INPUT_RECIPIENT)
            );
        },
    ],
    'domestic with pickup'                        => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withDeliveryOptionsWithPickupLocationInTheNetherlands()
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withRecipient(DEFAULT_INPUT_RECIPIENT)
            );
        },
    ],
    'row shipment'                                => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCustomsDeclaration(factory(CustomsDeclaration::class))
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withRecipient(['cc' => CountryCodes::CC_CA] + DEFAULT_INPUT_RECIPIENT)
            );
        },
    ],
    'shipment with weight in customs declaration' => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withRecipient(['cc' => CountryCodes::CC_US] + DEFAULT_INPUT_RECIPIENT)
                    ->withCustomsDeclaration(factory(CustomsDeclaration::class)->withWeight(1000))
            );
        },
    ],
    'eu shipment with pickup'                     => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withRecipient(['cc' => CountryCodes::CC_DE] + DEFAULT_INPUT_RECIPIENT)
                    ->withDeliveryOptionsWithPickupLocationInEU()
            );
        },
    ],
    'multicollo'                                  => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withDeliveryOptions(
                        factory(DeliveryOptions::class)
                            ->withLabelAmount(2)
                    )
                    ->withRecipient(DEFAULT_INPUT_RECIPIENT)
                    ->withReferenceIdentifier('my-multicollo-set')
            );
        },
    ],
    'multiple shipments'                          => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_POSTNL_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    ),
                factory(Shipment::class)->withCarrier(
                    factory(Carrier::class)
                        ->withId(Carrier::CARRIER_DHL_FOR_YOU_ID)
                        ->withOutboundFeatures(
                            factory(PropositionCarrierFeatures::class)->withEverything()
                        )
                )
            );
        },
    ],
    'GLS shipment'                                => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_GLS_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withRecipient(DEFAULT_INPUT_RECIPIENT)
            );
        },
    ],
    'GLS shipment with pickup'                    => [
        'input' => function () {
            return factory(ShipmentCollection::class)->push(
                factory(Shipment::class)
                    ->withCarrier(
                        factory(Carrier::class)
                            ->withId(Carrier::CARRIER_GLS_ID)
                            ->withOutboundFeatures(
                                factory(PropositionCarrierFeatures::class)->withEverything()
                            )
                    )
                    ->withDeliveryOptionsWithPickupLocationInTheNetherlands()
                    ->withRecipient(DEFAULT_INPUT_RECIPIENT)
            );
        },
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

it('direct prints', function ($input, $printerGroupId, $accept) {
    factory(Settings::class)
        ->withLabel(
            factory(LabelSettings::class)
                ->withDirectPrint(true)
                ->withPrinterGroupId($printerGroupId)
        )
        ->store();
    MockApi::enqueue(new ExamplePostIdsResponse());

    $repository = Pdk::get(ShipmentRepository::class);

    $response = $repository->createConcepts(new ShipmentCollection($input));
    $request  = MockApi::ensureLastRequest();

    $uri          = $request->getUri();
    $acceptHeader = Arr::first($request->getHeaders()['Accept']);

    expect($acceptHeader)
        ->toBe($accept)
        ->and($response)
        ->toBeInstanceOf(ShipmentCollection::class);
})->with([
    'missing printer group'   => [
        'input'          => [
            [
                'carrier'   => ['id' => Carrier::CARRIER_POSTNL_ID],
                'recipient' => DEFAULT_INPUT_RECIPIENT,
                'sender'    => DEFAULT_INPUT_SENDER,
            ],
        ],
        'printerGroupId' => null,
        'accept'         => null,
    ],
    'available printer group' => [
        'input'          => [
            [
                'carrier'   => ['id' => Carrier::CARRIER_POSTNL_ID],
                'recipient' => DEFAULT_INPUT_RECIPIENT,
                'sender'    => DEFAULT_INPUT_SENDER,
            ],
        ],
        'printerGroupId' => 'yakon-kaviaar-bliep',
        'accept'         => 'application/vnd.shipment_label+json+print;printer-group-id=yakon-kaviaar-bliep',
    ],
]);

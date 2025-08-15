<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\Account\Model\AccountGeneralSettings;
use MyParcelNL\Pdk\Api\Exception\ApiException;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollectionFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddress;
use MyParcelNL\Pdk\App\Order\Model\ShippingAddressFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Model\CarrierCapabilities;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierFeatures;
use MyParcelNL\Pdk\Proposition\Model\PropositionCarrierMetadata;
use MyParcelNL\Pdk\Proposition\Model\PropositionMetadata;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettingsFactory;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Collection\CustomsDeclarationItemCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\RetailLocationFactory;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkV2Response;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrdersResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsValidationErrorResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use MyParcelNL\Pdk\Tests\Uses\UsesSettingsMock;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance(), new UsesApiMock(), new UsesNotificationsMock(), new UsesSettingsMock());

dataset('order mode toggle', [
    'default'    => [false],
    'order mode' => [true],
]);

dataset('action type toggle', [
    'auto'   => 'automatic',
    'manual' => 'manual',
]);

it('handles auto exported flag', function (?string $actionType) {
    $orderFactory = factory(PdkOrderCollection::class)->push(
        factory(PdkOrder::class)->toTheNetherlands()
    );
    $orders       = new Collection($orderFactory->make());

    $orderFactory->store();

    MockApi::enqueue(new ExamplePostShipmentsResponse());
    MockApi::enqueue(new ExampleGetShipmentLabelsLinkResponse());
    MockApi::enqueue(new ExamplePostShipmentsResponse());

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'actionType' => $actionType,
        'orderIds'   => $orders
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    expect(json_decode($response->getContent(), false)->data->orders[0]->autoExported)->toBe(
        'automatic' === $actionType
    );

    // if it was already auto-exported, you can not auto-export again
    $orderFactory = factory(PdkOrderCollection::class)->push(
        factory(PdkOrder::class)->toTheNetherlands()->withAutoExported('automatic' === $actionType)
    );
    $orders       = new Collection($orderFactory->make());

    $orderFactory->store();

    MockApi::enqueue(new ExamplePostShipmentsResponse());

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'actionType' => 'automatic',
        'orderIds'   => $orders
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    expect(count(json_decode($response->getContent(), false)->data->orders[0]->shipments))->toBe('automatic' === $actionType ? 0 : 1);

    // if it was already auto-exported, you can manually export it no problem
    $orderFactory = factory(PdkOrderCollection::class)->push(
        factory(PdkOrder::class)->toTheNetherlands()->withAutoExported('automatic' === $actionType)
    );
    $orders       = new Collection($orderFactory->make());

    $orderFactory->store();

    MockApi::enqueue(new ExamplePostShipmentsResponse());

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'actionType' => 'manual',
        'orderIds'   => $orders
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    expect(count(json_decode($response->getContent(), false)->data->orders[0]->shipments))->toBe(1);
})
    ->with('action type toggle');

it('exports order', function (
    bool                      $orderMode,
    CarrierSettingsFactory    $carrierSettingsFactory,
    PdkOrderCollectionFactory $orderFactory
) {
    $orders = new Collection($orderFactory->make());

    $orderFactory->store();

    $carriers = $orders
        ->pluck('deliveryOptions.carrier.externalIdentifier')
        ->toArray();

    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withOrderMode($orderMode))
        ->withCarriers($carriers, $carrierSettingsFactory)
        ->store();

    MockApi::enqueue(
        ...$orderMode
        ? [new ExamplePostOrdersResponse(), new ExamplePostOrderNotesResponse()]
        : [new ExamplePostShipmentsResponse()]
    );

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => $orders
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    $lastRequest = MockApi::ensureLastRequest();

    assertMatchesJsonSnapshot(
        $lastRequest->getBody()
            ->getContents()
    );

    $content = json_decode($response->getContent(), true);

    $responseOrders    = $content['data']['orders'];
    $responseShipments = Arr::pluck($responseOrders, 'shipments');

    $errors = Notifications::all()
        ->filter(function (Notification $notification) {
            return $notification->variant === Notification::VARIANT_ERROR;
        });

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($responseOrders)
        ->toHaveLength(count($orders))
        ->and($response->getStatusCode())
        ->toBe(200)
        // Expect no errors to have been added to notifications
        ->and($errors->toArrayWithoutNull())
        ->toBe([]);

    if ($orderMode) {
        expect($responseShipments)->each->toHaveLength(0);
    } else {
        expect($responseShipments)->each->toHaveLength(1)
            ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
    }
})
    ->with('order mode toggle') // data sets defined in "tests/Datasets"
    ->with('carrier export settings')
    ->with('pdk orders domestic');

it('merges partial payload with existing order', function (
    bool                      $orderMode,
    CarrierSettingsFactory    $carrierSettingsFactory,
    PdkOrderCollectionFactory $orderFactory
) {

    $orders = new Collection($orderFactory->make());

    $orderFactory->store();

    $carriers = $orders
        ->pluck('deliveryOptions.carrier.externalIdentifier')
        ->toArray();

    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withOrderMode($orderMode))
        ->withCarriers($carriers, $carrierSettingsFactory)
        ->store();

    MockApi::enqueue(
        ...$orderMode
        ? [new ExamplePostOrdersResponse(), new ExamplePostOrderNotesResponse()]
        : [new ExamplePostShipmentsResponse()]
    );

    $date = new \DateTime('+1 day');
    $partialDeliveryOptions = [
        DeliveryOptions::DATE => $date
    ];

    /**
     * @var DeliveryOptions $existingDeliveryOptions
     */
    $existingDeliveryOptions = $orders->pluck('deliveryOptions')->first();
    $mergedDeliveryOptions = $existingDeliveryOptions
        ->fill($partialDeliveryOptions)
        ->toArrayWithoutNull();

    expect($mergedDeliveryOptions[DeliveryOptions::DATE])->toBe($date->format(Pdk::get('defaultDateFormat')));

    $requestWithPayload = new Request(
        ['action' => PdkBackendActions::EXPORT_ORDERS, 'orderIds' => $orders->pluck('externalIdentifier')->toArray()],
        [],
        [],
        [],
        [],
        [],
        json_encode(
            [
            'data' => [
                'orders' => [
                    [
                        'deliveryOptions' => $partialDeliveryOptions
                    ],
                ],
            ],
        ]
        )
    );

    $response = Actions::execute($requestWithPayload);

    $content = json_decode($response->getContent(), true);

    $responseOrders    = $content['data']['orders'];
    $responseShipments = Arr::pluck($responseOrders, 'shipments');

    $errors = Notifications::all()
        ->filter(function (Notification $notification) {
            return $notification->variant === Notification::VARIANT_ERROR;
        });

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($responseOrders)
        ->toHaveLength(count($orders))
        // Check to make sure the carrier did not reset to the default - this is the only part that is easy to test due to not being affected by calculators
        ->and($responseOrders[0]['deliveryOptions'][DeliveryOptions::CARRIER])
        ->toBe($mergedDeliveryOptions[DeliveryOptions::CARRIER])
        ->and($response->getStatusCode())
        ->toBe(200)
        // Expect no errors to have been added to notifications
        ->and($errors->toArrayWithoutNull())
        ->toBe([]);

    if ($orderMode) {
        expect($responseShipments)->each->toHaveLength(0);
    } else {
        expect($responseShipments)->each->toHaveLength(1)
            ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
    }

})
    ->with('order mode toggle')
    ->with('carrier export settings')
    ->with('pdk orders domestic');
;

it('exports multicollo order', function (
    PdkOrderCollectionFactory $orderFactory,
    int                       $expectedNumberOfShipments
) {
    $orders = new Collection($orderFactory->make());

    $orderFactory->store();

    $carriers = $orders
        ->pluck('deliveryOptions.carrier.externalIdentifier')
        ->toArray();

    factory(Settings::class)
        ->withCarriers($carriers)
        ->store();

    MockApi::enqueue(new ExamplePostShipmentsResponse());

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => $orders
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    $lastRequest = MockApi::ensureLastRequest();

    assertMatchesJsonSnapshot(
        $lastRequest->getBody()
            ->getContents()
    );

    $content = json_decode($response->getContent(), true);

    $responseOrders    = $content['data']['orders'];
    $responseShipments = Arr::pluck($responseOrders, 'shipments');

    $errors = Notifications::all()
        ->filter(function (Notification $notification) {
            return $notification->variant === Notification::VARIANT_ERROR;
        });

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($responseOrders)
        ->toHaveLength(count($orders))
        ->and($response->getStatusCode())
        ->toBe(200)
        // Expect no errors to have been added to notifications
        ->and($errors->toArrayWithoutNull())
        ->toBe([])
        ->and($responseShipments)->each->toHaveLength($expectedNumberOfShipments)
        ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
})
    ->with('multicolloPdkOrders');

it('adds api errors as notifications if shipment export fails', function () {
    $errorResponse = new ExamplePostShipmentsValidationErrorResponse();
    MockApi::enqueue($errorResponse);

    factory(CarrierSettings::class, Carrier::CARRIER_POSTNL_NAME)->store();
    factory(PdkOrder::class)
        ->withExternalIdentifier('error')
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier(Carrier::CARRIER_DHL_FOR_YOU_NAME)
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
        )
        ->store();

    try {
        $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, ['orderIds' => 'error']);
        expect($response->getStatusCode())->toBe(400);
    } catch (ApiException $e) {
        $expectedErrorContent = $errorResponse->getContent();
        expect($e->getMessage())->toBe(
            'Request failed. Status code: ' . $expectedErrorContent['status_code'] . '. Message: Shipment validation error (request_id: ' . $expectedErrorContent['request_id'] . ')'
        );
        $notifications = Notifications::all()
            ->toArrayWithoutNull();

        $notification = Arr::first($notifications);

        expect($notifications)
            ->toHaveLength(1)
            ->and($notification)
            ->toHaveKeysAndValues([
                'title'    => 'Could not create shipment',
                'content'  => [
                    'data.shipments[0].options.return shipment option not supported',
                ],
                'variant'  => Notification::VARIANT_ERROR,
                'category' => Notification::CATEGORY_ACTION,
                'timeout'  => false,
                'tags'     => [
                    'action'   => PdkBackendActions::EXPORT_ORDERS,
                    'orderIds' => 'error',
                    'request_id' => $expectedErrorContent['request_id'],
                    'errors' => $expectedErrorContent['errors']
                ],
            ]);
    }

});

it('exports order and directly returns barcode if concept shipments is off', function () {
    factory(Settings::class)
        ->withOrder(factory(OrderSettings::class)->withConceptShipments(false))
        ->withCarrier(Carrier::CARRIER_POSTNL_NAME)
        ->store();

    $collection = factory(PdkOrderCollection::class, 1)
        ->store()
        ->make();

    MockApi::enqueue(
        new ExamplePostShipmentsResponse(),
        new ExampleGetShipmentLabelsLinkV2Response(),
        new ExampleGetShipmentsResponse()
    );

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($collection->toArray(), 'externalIdentifier'),
    ]);

    $content = json_decode($response->getContent(), true);

    $responseOrders    = $content['data']['orders'];
    $responseShipments = Arr::pluck($responseOrders, 'shipments');

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($responseOrders)
        ->toHaveLength(count($responseOrders))
        ->and($response->getStatusCode())
        ->toBe(200)
        ->and($responseShipments)->each->toHaveLength(1)
        ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
});

it(
    'exports pickup order without signature',
    function (?RetailLocationFactory $pickupLocation, ShippingAddressFactory $shippingAddress) {
        factory(CarrierSettings::class)
            ->withId((string) Carrier::CARRIER_POSTNL_ID)
            ->withExportSignature(false)
            ->store();

        $orderWithPickup = factory(PdkOrder::class)
            ->withOrderDate('2020-01-01T00:00:00+00:00')
            ->withDeliveryOptionsWithPickupLocation($pickupLocation)
            ->withShippingAddress($shippingAddress)
            ->store()
            ->make();

        $collection = factory(PdkOrderCollection::class)
            ->push($orderWithPickup)
            ->store()
            ->make();

        MockApi::enqueue(new ExamplePostShipmentsResponse());

        $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
            'orderIds' => Arr::pluck($collection->toArray(), 'externalIdentifier'),
        ]);

        $content = json_decode($response->getContent(), true);

        $responseOrders    = $content['data']['orders'];
        $responseShipments = Arr::pluck($responseOrders, 'shipments');

        expect($response)
            ->toBeInstanceOf(Response::class)
            ->and($responseOrders)
            ->toHaveLength(count($responseOrders))
            ->and($response->getStatusCode())
            ->toBe(200)
            ->and($responseShipments)->each->toHaveLength(1)
            ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
    }
)
    ->with(
        [
            'dutch shipping location'   => [
                function () {
                    return factory(RetailLocation::class)->inTheNetherlands();
                },
                function () {
                    return factory(ShippingAddress::class)->inTheNetherlands();
                },
            ],
            'foreign shipping location' => [
                null,
                function () {
                    return factory(ShippingAddress::class)->inTheUnitedKingdom();
                },
            ],
        ]
    );

it(
    'exports evening order',
    function (ShippingAddressFactory $shippingAddress) {
        factory(CarrierSettings::class)
            ->withId((string) Carrier::CARRIER_POSTNL_ID)
            ->store();

        $order = factory(PdkOrder::class)
            ->withOrderDate('2020-01-01T00:00:00+00:00')
            ->withDeliveryOptions(
                factory(DeliveryOptions::class)
                    ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
            )
            ->withShippingAddress($shippingAddress)
            ->store()
            ->make();

        $collection = factory(PdkOrderCollection::class)
            ->push($order)
            ->store()
            ->make();

        MockApi::enqueue(new ExamplePostShipmentsResponse());

        $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
            'orderIds' => Arr::pluck($collection->toArray(), 'externalIdentifier'),
        ]);

        $content = json_decode($response->getContent(), true);

        $responseOrders    = $content['data']['orders'];
        $responseShipments = Arr::pluck($responseOrders, 'shipments');

        expect($response)
            ->toBeInstanceOf(Response::class)
            ->and($responseOrders)
            ->toHaveLength(count($responseOrders))
            ->and($response->getStatusCode())
            ->toBe(200)
            ->and($responseShipments)->each->toHaveLength(1)
            ->and(Arr::pluck($responseShipments[0], 'id'))->each->toBeInt();
    }
)
    ->with(
        [
            'foreign shipping location' =>
                function () {
                    return factory(ShippingAddress::class)->inTheUnitedKingdom();
                },
        ]
    );

it(
    'exports international orders',
    function (
        PdkOrderCollectionFactory $factory,
        bool                      $accountHasCarrierSmallPackageContract,
        bool                      $carrierHasInternationalMailboxAllowed,
        bool                      $orderMode
    ) {
        MockApi::enqueue(
            ...$orderMode
            ? [new ExamplePostOrdersResponse(), new ExamplePostOrderNotesResponse()]
            : [new ExamplePostShipmentsResponse()]
        );

        $collection  = $factory
            ->store()
            ->make();
        $fakeCarrier = $collection->first()->deliveryOptions->carrier;

        factory(CarrierSettings::class, $fakeCarrier->externalIdentifier)
            ->withAllowInternationalMailbox($carrierHasInternationalMailboxAllowed)
            ->store();

        factory(OrderSettings::class)
            ->withConceptShipments(true)
            ->store();

        factory(AccountGeneralSettings::class)
            ->withHasCarrierSmallPackageContract($accountHasCarrierSmallPackageContract)
            ->store();

        $orderIds = $collection->pluck('externalIdentifier');

        Actions::execute(PdkBackendActions::EXPORT_ORDERS, ['orderIds' => $orderIds->toArray()]);

        $lastRequest = MockApi::ensureLastRequest();
        $stream      = $lastRequest->getBody();

        assertMatchesJsonSnapshot($stream->getContents());
    }
)
    ->with([
        'without customs declaration' => [
            function () {
                return factory(PdkOrderCollection::class)->push(factory(PdkOrder::class)->toTheUnitedStates());
            },
            'accountHasCarrierSmallPackageContract' => false,
            'carrierHasInternationalMailboxAllowed' => false,
        ],

        'with customs declaration (deprecated)' => [
            function () {
                return factory(PdkOrderCollection::class)->push(
                    factory(PdkOrder::class)
                        ->toTheUnitedStates()
                        ->withCustomsDeclaration(
                            factory(CustomsDeclaration::class)
                                ->withContents(3)
                                ->withWeight(3000)
                                ->withItems(
                                    factory(CustomsDeclarationItemCollection::class)->push(
                                        factory(CustomsDeclarationItem::class)
                                            ->withWeight(400)
                                            ->withAmount(3)
                                            ->withItemValue(1000)
                                            ->withDescription('hello')
                                            ->withCountry('DE')
                                            ->withClassification('123456')
                                    )
                                )
                        )
                );
            },
            'accountHasCarrierSmallPackageContract' => false,
            'carrierHasInternationalMailboxAllowed' => false,
        ],

        'custom postnl with international mailbox to Belgium' => [
            function () {
                return factory(PdkOrderCollection::class)->push(
                    factory(PdkOrder::class)
                        ->toBelgium()
                        ->withDeliveryOptions(
                            factory(DeliveryOptions::class)
                                ->withCarrier(
                                    factory(Carrier::class)
                                        ->fromPostNL()
                                        ->withContractId(123456)
                                        ->withOutboundFeatures(
                                            factory(PropositionCarrierFeatures::class)
                                                ->withMetadata(
                                                    ['carrierSmallPackageContract' => PropositionCarrierMetadata::FEATURE_CUSTOM_CONTRACT_ONLY]
                                                )
                                                ->withPackageTypes([PropositionCarrierFeatures::PACKAGE_TYPE_MAILBOX_NAME])
                                        )
                                )
                                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
                        )
                );
            },
            'accountHasCarrierSmallPackageContract' => true,
            'carrierHasInternationalMailboxAllowed' => true,
        ],

        'custom postnl with international mailbox' => [
            function () {
                return factory(PdkOrderCollection::class)->push(
                    factory(PdkOrder::class)
                        ->toGermany()
                        ->withDeliveryOptions(
                            factory(DeliveryOptions::class)
                                ->withCarrier(
                                    factory(Carrier::class)
                                        ->fromPostNL()
                                        ->withContractId(123456)
                                        ->withOutboundFeatures(
                                            factory(PropositionCarrierFeatures::class)
                                                ->withFeatures(
                                                    ['carrierSmallPackageContract' => PropositionCarrierMetadata::FEATURE_CUSTOM_CONTRACT_ONLY]
                                                )
                                                ->withPackageTypes([PropositionCarrierFeatures::PACKAGE_TYPE_MAILBOX_NAME])
                                        )
                                )
                                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
                        )
                );
            },
            'accountHasCarrierSmallPackageContract' => true,
            'carrierHasInternationalMailboxAllowed' => true,
        ],

        'postnl with international mailbox filtered out' => [
            function () {
                return factory(PdkOrderCollection::class)->push(
                    factory(PdkOrder::class)
                        ->toGermany()
                        ->withDeliveryOptions(
                            factory(DeliveryOptions::class)
                                ->withCarrier(factory(Carrier::class)->fromPostNL())
                                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
                        )
                );
            },
            'accountHasCarrierSmallPackageContract' => true,
            'carrierHasInternationalMailboxAllowed' => true,
        ],

        'ups standard' => [
            function () {
                return factory(PdkOrderCollection::class)->push(
                    factory(PdkOrder::class)
                        ->toTheUnitedStates()
                        ->withDeliveryOptions(
                            factory(DeliveryOptions::class)
                                ->withCarrier(factory(Carrier::class)->fromUpsStandard())
                        )
                );
            },
            'accountHasCarrierSmallPackageContract' => false,
            'carrierHasInternationalMailboxAllowed' => false,
        ],
    ])
    ->with('order mode toggle');

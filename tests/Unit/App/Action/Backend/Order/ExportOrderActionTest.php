<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollectionFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Audit\Contract\AuditServiceInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettingsFactory;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Shipment\Collection\CustomsDeclarationItemCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclarationItem;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentLabelsLinkV2Response;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrdersResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use MyParcelNL\Pdk\Tests\Uses\UsesNotificationsMock;
use MyParcelNL\Pdk\Tests\Uses\UsesSettingsMock;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance(), new UsesApiMock(), new UsesNotificationsMock(), new UsesSettingsMock());

dataset('orderModeToggle', [
    'default'    => [false],
    'order mode' => [true],
]);

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
    ->with('orderModeToggle')
    ->with('carrierExportSettings')
    ->with('pdkOrdersDomestic');

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

it('adds notification if shipment export fails', function () {
    MockApi::enqueue(new ExamplePostShipmentsResponse());

    factory(CarrierSettings::class, Carrier::CARRIER_POSTNL_NAME)->store();
    factory(PdkOrder::class)
        ->withExternalIdentifier('error')
        ->withDeliveryOptions(
            factory(DeliveryOptions::class)
                ->withCarrier(Carrier::CARRIER_DHL_FOR_YOU_NAME)
                ->withDeliveryType(DeliveryOptions::DELIVERY_TYPE_EVENING_NAME)
        )
        ->store();

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, ['orderIds' => 'error']);

    expect($response->getStatusCode())->toBe(200);

    $notifications = Notifications::all()
        ->toArrayWithoutNull();

    $notification = Arr::first($notifications);

    expect($notifications)
        ->toHaveLength(1)
        ->and($notification)
        ->toHaveKeysAndValues([
            'title'    => 'Failed to export order error',
            'variant'  => Notification::VARIANT_ERROR,
            'category' => Notification::CATEGORY_ACTION,
            'timeout'  => false,
            'tags'     => [
                'action'   => PdkBackendActions::EXPORT_ORDERS,
                'orderIds' => 'error',
            ],
        ])
        ->and($notification['content'][0] ?? null)
        ->toBe(
            'deliveryOptions.deliveryType: Does not have a value in the enumeration ["standard","pickup",null]'
        );
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

it('exports pickup order without signature', function () {
    factory(CarrierSettings::class)
        ->withId((string) Carrier::CARRIER_POSTNL_ID)
        ->withExportSignature(false)
        ->store();

    $orderWithPickup = factory(PdkOrder::class)
        ->withOrderDate('2020-01-01T00:00:00+00:00')
        ->withDeliveryOptionsWithPickupLocation()
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
});

it('exports international orders', function (PdkOrderCollectionFactory $factory, bool $orderMode) {
    MockApi::enqueue(
        ...$orderMode
        ? [new ExamplePostOrdersResponse(), new ExamplePostOrderNotesResponse()]
        : [new ExamplePostShipmentsResponse()]
    );

    factory(OrderSettings::class)
        ->withConceptShipments(true)
        ->store();

    $collection = $factory
        ->store()
        ->make();

    $orderIds = $collection->pluck('externalIdentifier');

    Actions::execute(PdkBackendActions::EXPORT_ORDERS, ['orderIds' => $orderIds->toArray()]);

    $lastRequest = MockApi::ensureLastRequest();
    $stream      = $lastRequest->getBody();

    assertMatchesJsonSnapshot($stream->getContents());
})
    ->with([
        'without customs declaration' => [
            function () {
                return factory(PdkOrderCollection::class)->push(factory(PdkOrder::class)->toTheUnitedStates());
            },
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
                                        ->withCapabilities([
                                            'internationalMailbox' => true,
                                        ])
                                )
                                ->withPackageType(DeliveryOptions::PACKAGE_TYPE_MAILBOX_NAME)
                        )
                );
            },
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
        ],
    ])
    ->with('orderModeToggle');

it('creates audit after export', function () {
    factory(OrderSettings::class)
        ->withConceptShipments(true)
        ->store();

    $order = factory(PdkOrder::class)
        ->store()
        ->make();

    MockApi::enqueue(new ExamplePostShipmentsResponse(), new ExampleGetShipmentsResponse());

    Actions::execute(PdkBackendActions::EXPORT_ORDERS, ['orderIds' => [$order->externalIdentifier]]);

    $auditService = Pdk::get(AuditServiceInterface::class);
    $audit        = $auditService->all()
        ->first();

    expect($audit)->not->toBeNull()
        ->and($audit->modelIdentifier)
        ->toBe($order->externalIdentifier)
        ->and($audit->model)
        ->toBe(PdkOrder::class)
        ->and($audit->action)
        ->toBe(PdkBackendActions::EXPORT_ORDERS)
        ->and($audit->arguments)
        ->toBe([
            'mode' => 'shipment',
        ]);
});

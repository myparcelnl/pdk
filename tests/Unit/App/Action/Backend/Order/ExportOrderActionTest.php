<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\CarrierSettingsFactory;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
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
    bool                   $orderMode,
    CarrierSettingsFactory $carrierSettingsFactory,
    array                  $orders
) {
    $carriers = Arr::pluck($orders, 'deliveryOptions.carrier.externalIdentifier');

    factory(Settings::class)
        ->withGeneral(factory(GeneralSettings::class)->withOrderMode($orderMode))
        ->withCarriers($carriers, $carrierSettingsFactory)
        ->store();

    factory(PdkOrderCollection::class)
        ->push(...$orders)
        ->store();

    MockApi::enqueue(
        ...$orderMode
        ? [new ExamplePostOrdersResponse(), new ExamplePostOrderNotesResponse()]
        : [new ExamplePostShipmentsResponse()]
    );

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($orders, 'externalIdentifier'),
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

it('exports order without customer information if setting is false', function (
    bool  $share,
    bool  $orderMode,
    array $orders
) {
    $carriers = Arr::pluck($orders, 'deliveryOptions.carrier.externalIdentifier');

    factory(Settings::class)
        ->withGeneral(
            factory(GeneralSettings::class)
                ->withOrderMode($orderMode)
                ->withShareCustomerInformation($share)
        )
        ->withCarriers($carriers)
        ->store();

    $collection = factory(PdkOrderCollection::class)
        ->push(...$orders)
        ->store()
        ->make();

    MockApi::enqueue($orderMode ? new ExamplePostOrdersResponse() : new ExamplePostShipmentsResponse());

    Actions::execute(PdkBackendActions::EXPORT_ORDERS, [
        'orderIds' => Arr::pluck($orders, 'externalIdentifier'),
    ]);

    $lastRequest = MockApi::ensureLastRequest();

    $content = json_decode(
        $lastRequest->getBody()
            ->getContents(),
        true
    );

    $postedAddress = Arr::get(
        $content,
        $orderMode
            ? 'data.orders.0.invoice_address'
            : 'data.shipments.0.recipient'
    );

    if ($orderMode && ! $collection->contains('billingAddress', '!=', null)) {
        expect($postedAddress)->toBeNull();
        return;
    }

    expect($postedAddress)->toBeArray();

    if ($share) {
        expect(Arr::only($postedAddress, ['email', 'phone']))
            ->each->toBeString();
    } else {
        expect(Arr::only($postedAddress, ['email', 'phone']))
            ->each->toBeNull();
    }
})
    ->with([
        'share'        => [
            'share'     => true,
            'orderMode' => false,
        ],
        'do not share' => [
            'share'     => false,
            'orderMode' => false,
        ],

        'order mode: share'        => [
            'share'     => true,
            'orderMode' => true,
        ],
        'order mode: do not share' => [
            'share'     => false,
            'orderMode' => true,
        ],
    ])
    ->with('pdkOrdersDomestic');

it('adds notification if shipment export fails', function () {
    MockApi::enqueue(new ExamplePostShipmentsResponse());

    factory(CarrierSettings::class, Carrier::CARRIER_POSTNL_NAME)->store();
    factory(PdkOrder::class)
        ->withExternalIdentifier('error')
        ->withShippingAddress(['cc' => null])
        ->store();

    $response = Actions::execute(PdkBackendActions::EXPORT_ORDERS, ['orderIds' => 'error']);

    expect($response->getStatusCode())->toBe(200);

    $notifications = Notifications::all()
        ->toArray();

    expect($notifications)
        ->toHaveLength(1)
        ->and($notifications)->each->toEqual([
            'title'    => 'Failed to export order error',
            'content'  => [
                'shippingAddress.cc: NULL value found, but a string is required',
            ],
            'variant'  => Notification::VARIANT_ERROR,
            'category' => 'api',
            'timeout'  => false,
        ]);
});

it('exports order and directly returns barcode if concept shipments is off', function () {
    factory(Settings::class)
        ->withGeneral(factory(GeneralSettings::class)->withConceptShipments(false))
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

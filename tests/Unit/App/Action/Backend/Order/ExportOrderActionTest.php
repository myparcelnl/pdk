<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Contract\SettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;
use MyParcelNL\Pdk\Settings\Model\GeneralSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrdersResponse;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockSettingsRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance(), new UsesApiMock());

dataset('orderModeToggle', [
    'default'    => [false],
    'order mode' => [true],
]);

it('exports order', function (
    bool  $orderMode,
    array $carrierSettings,
    array $orders
) {
    /** @var MockPdkOrderRepository $pdkOrderRepository */
    $pdkOrderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    /** @var MockSettingsRepository $settingsRepository */
    $settingsRepository = Pdk::get(SettingsRepositoryInterface::class);

    $collection = new PdkOrderCollection($orders);
    $pdkOrderRepository->updateMany($collection);
    $settingsRepository->storeAllSettings(
        new Settings([
            GeneralSettings::ID => [GeneralSettings::ORDER_MODE => $orderMode],
            CarrierSettings::ID => $collection->pluck('deliveryOptions.carrier.externalIdentifier')
                ->unique()
                ->mapWithKeys(static function ($carrier) use ($carrierSettings) {
                    return [$carrier => $carrierSettings];
                })
                ->all(),
        ])
    );

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

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($responseOrders)
        ->toHaveLength(count($orders))
        ->and($response->getStatusCode())
        ->toBe(200);

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
    /** @var MockPdkOrderRepository $pdkOrderRepository */
    $pdkOrderRepository = Pdk::get(PdkOrderRepositoryInterface::class);
    /** @var MockSettingsRepository $settingsRepository */
    $settingsRepository = Pdk::get(SettingsRepositoryInterface::class);

    $collection = new PdkOrderCollection($orders);

    $pdkOrderRepository->updateMany($collection);
    $settingsRepository->storeSettings(
        new GeneralSettings([
            GeneralSettings::ORDER_MODE                 => $orderMode,
            GeneralSettings::SHARE_CUSTOMER_INFORMATION => $share,
        ])
    );

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

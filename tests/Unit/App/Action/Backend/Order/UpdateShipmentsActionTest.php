<?php
/** @noinspection PhpUnhandledExceptionInspection */

/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Tests\Api\Response\ExampleGetShipmentsResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Response;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance(), new UsesApiMock());

it('updates shipments', function () {
    MockApi::enqueue(
        new ExampleGetShipmentsResponse()
    );

    $response = Actions::execute(PdkBackendActions::UPDATE_SHIPMENTS, [
        'orderIds' => ['263', '264'],
    ]);

    $content = json_decode($response->getContent(), true);

    Arr::forget($content, 'data.shipments.0.updated');

    assertMatchesJsonSnapshot(json_encode($content));

    expect($response)
        ->toBeInstanceOf(Response::class)
        ->and($response->getStatusCode())
        ->toBe(200);
});

it('updates barcode in note', function () {
    $collection = factory(PdkOrderCollection::class)
        ->push(
            factory(PdkOrder::class)->withShipments([factory(Shipment::class)->withId()]),
            factory(PdkOrder::class)->withShipments([factory(Shipment::class)->withId()])
        )
        ->store()
        ->make();

    $ids = Arr::pluck(
        (new Collection($collection->toArray()))
            ->pluck('shipments')
            ->flatten(1)
            ->toArray(),
        'id'
    );

    MockApi::enqueue(
        new ExampleGetShipmentsResponse([
            array_replace(ExampleGetShipmentsResponse::DEFAULT_SHIPMENT_DATA, [
                'id'      => $ids[0],
                'barcode' => '3SMYPA000000000',
            ]),
            array_replace(ExampleGetShipmentsResponse::DEFAULT_SHIPMENT_DATA, [
                'id'      => $ids[1],
                'barcode' => '3SMYPA000000001',
            ]),
        ])
    );

    $orderIds = (new Collection($collection))
        ->pluck('externalIdentifier')
        ->toArray();

    factory(OrderSettings::class)
        ->withBarcodeInNote(true)
        ->store();

    Actions::execute(PdkBackendActions::UPDATE_SHIPMENTS, [
        'orderIds' => $orderIds,
    ]);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderNoteRepository $notesRepository */
    $notesRepository = Pdk::get(PdkOrderNoteRepositoryInterface::class);

    expect($collection->count())
        ->toBeGreaterThan(0);

    $collection->each(function (PdkOrder $order) use ($notesRepository) {
        $notes = $notesRepository->getFromOrder($order);

        expect($notes->count())
            ->toBe(1)
            ->and($notes->first()->orderIdentifier)
            ->toBe($order->externalIdentifier)
            ->and($notes->first()->barcode)
            ->toBe($order->shipments->first()->barcode);
    });
});

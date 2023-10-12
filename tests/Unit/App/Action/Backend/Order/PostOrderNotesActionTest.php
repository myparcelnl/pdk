<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\Account\Model\Account;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollectionFactory;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollectionFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Tests\Api\Response\ExamplePostOrderNotesResponse;
use MyParcelNL\Pdk\Tests\Bootstrap\MockApi;
use MyParcelNL\Pdk\Tests\Bootstrap\TestBootstrapper;
use MyParcelNL\Pdk\Tests\Factory\Collection\FactoryCollection;
use MyParcelNL\Pdk\Tests\Uses\UsesApiMock;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesEachMockPdkInstance(), new UsesApiMock());

it('posts order notes if order has notes', function (
    PdkOrderCollectionFactory     $ordersFactory,
    PdkOrderNoteCollectionFactory $notesFactory
) {
    TestBootstrapper::hasApiKey();

    (new FactoryCollection([
        $ordersFactory,
        $notesFactory,
        factory(OrderSettings::class)->withOrderMode(true),
        factory(Account::class)->withSubscriptionFeatures([Account::FEATURE_ORDER_NOTES]),
    ]))->store();

    $orderCollection = new Collection($ordersFactory->make());

    MockApi::enqueue(new ExamplePostOrderNotesResponse());

    Actions::execute(PdkBackendActions::POST_ORDER_NOTES, [
        'orderIds' => $orderCollection
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    $request = MockApi::getLastRequest();

    if ($orderCollection->contains('apiIdentifier', '==', null)) {
        expect($request)->toBeNull();
        return;
    }

    expect($request)->not->toBeNull();
})->with([
    'single order' => [
        function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withApiIdentifier('90001')
                    ->withExternalIdentifier('243')
                    ->withSimpleDeliveryOptions()
            );
        },

        function () {
            return factory(PdkOrderNoteCollection::class)->push(
                factory(PdkOrderNote::class)
                    ->byWebshop()
                    ->withOrderIdentifier('243'),
                factory(PdkOrderNote::class)
                    ->byCustomer()
                    ->withOrderIdentifier('243')
            );
        },
    ],

    'two orders where only one has notes' => [
        function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withExternalIdentifier('245')
                    ->withApiIdentifier('90002'),
                factory(PdkOrder::class)
                    ->withExternalIdentifier('247')
                    ->withApiIdentifier('90003')
            );
        },

        function () {
            return factory(PdkOrderNoteCollection::class)->push(
                factory(PdkOrderNote::class)
                    ->byCustomer()
                    ->withOrderIdentifier('247')
            );
        },
    ],

    'order without api identifier' => [
        function () {
            return factory(PdkOrderCollection::class)->push(
                factory(PdkOrder::class)
                    ->withExternalIdentifier('248')
            );
        },

        function () {
            return factory(PdkOrderNoteCollection::class)->push(
                factory(PdkOrderNote::class)
                    ->byCustomer()
                    ->withOrderIdentifier('248')
            );
        },
    ],
]);

it('does not post order notes if account does not have feature', function (PdkOrderCollectionFactory $factory) {
    TestBootstrapper::hasAccount();

    $orderCollection = $factory->store()
        ->make();

    factory(OrderSettings::class)
        ->withOrderMode(true)
        ->store();

    Actions::execute(PdkBackendActions::POST_ORDER_NOTES, [
        'orderIds' => (new Collection($orderCollection))
            ->pluck('externalIdentifier')
            ->toArray(),
    ]);

    $request = MockApi::getLastRequest();

    expect($request)->toBeNull();
})->with([
    'single exported order with one order note' => function () {
        return factory(PdkOrderCollection::class)->push(
            factory(PdkOrder::class)
                ->withApiIdentifier('90001')
                ->withNotes()
        );
    },
]);

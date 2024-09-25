<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderFactory;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\App\Order\Model\PdkProduct;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\factory;
use function MyParcelNL\Pdk\Tests\usesShared;
use function Spatie\Snapshots\assertMatchesJsonSnapshot;

usesShared(new UsesMockPdkInstance());

it('creates fulfilment order from pdk order', function (PdkOrderFactory $factory) {
    $product1 = factory(PdkProduct::class)
        ->withEverything()
        ->withPrice(100);
    $product2 = factory(PdkProduct::class)
        ->withEverything()
        ->withPrice(200);

    $product3 = factory(PdkProduct::class)
        ->withEverything()
        ->withPrice(300);

    $factory->withLines(
        factory(PdkOrderLineCollection::class)->push(
            factory(PdkOrderLine::class)
                ->withProduct($product1)
                ->withQuantity(3)
                ->withPrice(3 * 100)
                ->withVat((int) (3 * 100 * 0.09)),

            factory(PdkOrderLine::class)
                ->withProduct($product2)
                ->withPrice(300)
                ->withVat((int) (300 * 0.21)),

            factory(PdkOrderLine::class)
                ->withProduct($product3)
                ->withQuantity(6)
                ->withPrice(6 * 300)
                ->withVat((int) (6 * 300 * 0.21))
        )
    );

    $pdkOrder        = $factory->make();
    $fulfilmentOrder = Order::fromPdkOrder($pdkOrder);

    expect($fulfilmentOrder)->toBeInstanceOf(Order::class);
    assertMatchesJsonSnapshot(json_encode($fulfilmentOrder->toArrayWithoutNull()));
})->with([
        'simple order' => function () {
            return factory(PdkOrder::class);
        },

        'order with delivery options' => function () {
            return factory(PdkOrder::class)->withDeliveryOptionsWithAllOptions();
        },

        'order to pickup location' => function () {
            return factory(PdkOrder::class)->withDeliveryOptionsWithPickupLocation();
        },

        'order to germany' => function () {
            return factory(PdkOrder::class)->toGermany();
        },

    ]
);

it('returns empty fulfilment order when no pdk order is passed', function () {
    $fulfilmentOrder = Order::fromPdkOrder(null);
    expect($fulfilmentOrder)->toBeInstanceOf(Order::class);
    assertMatchesJsonSnapshot(json_encode($fulfilmentOrder->toArrayWithoutNull()));
});


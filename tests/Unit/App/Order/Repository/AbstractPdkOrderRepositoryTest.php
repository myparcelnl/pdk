<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;

it('gets a single order', function () {
    /** @var \MyParcelNL\Pdk\App\Order\Repository\MockPdkOrderRepository $repository */
    $repository = Pdk::get(PdkOrderRepositoryInterface::class);
    $order      = $repository->get(3);

    expect($order)->toBeInstanceOf(PdkOrder::class);
});

it('gets multiple orders', function () {
    /** @var \MyParcelNL\Pdk\App\Order\Repository\MockPdkOrderRepository $repository */
    $repository = Pdk::get(PdkOrderRepositoryInterface::class);
    $orders     = $repository->getMany(['1;2', 3]);

    expect($orders)
        ->toHaveLength(3)
        ->and(
            $orders->every(function ($order) {
                return is_a($order, PdkOrder::class);
            })
        )
        ->toBeTrue();
});

it('updates order', function () {
    /** @var \MyParcelNL\Pdk\App\Order\Repository\MockPdkOrderRepository $repository */
    $repository = Pdk::get(PdkOrderRepositoryInterface::class);
    $order      = new PdkOrder(['externalIdentifier' => 'PS-123']);
    $newOrder   = $repository->update($order);

    expect($newOrder)->toBeInstanceOf(PdkOrder::class);
});

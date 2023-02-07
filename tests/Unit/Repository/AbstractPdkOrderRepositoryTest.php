<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use function DI\autowire;

beforeEach(function () {
    $pdk = PdkFactory::create(
        MockPdkConfig::create(
            [
                AbstractPdkOrderRepository::class => autowire(MockPdkOrderRepository::class),
            ]
        )
    );

    /** @var \MyParcelNL\Pdk\Plugin\Repository\AbstractPdkOrderRepository $repository */
    $repository = Pdk::get(AbstractPdkOrderRepository::class);

    $this->repository = $repository;
});

it('gets a single order', function () {
    $order = $this->repository->get(3);

    expect($order)->toBeInstanceOf(PdkOrder::class);
});

it('gets multiple orders', function () {
    $orders = $this->repository->getMany(['1;2', 3]);

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
    $order    = new PdkOrder(['externalIdentifier' => 'PS-123']);
    $newOrder = $this->repository->update($order);

    expect($newOrder)->toBeInstanceOf(PdkOrder::class);
});

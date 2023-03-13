<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository;
use function DI\autowire;

beforeEach(function () {
    $pdk = PdkFactory::create(
        MockPdkConfig::create(
            [
                PdkOrderRepositoryInterface::class => autowire(
                    MockPdkOrderRepository::class
                ),
            ]
        )
    );

    /** @var \MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface $repository */
    $repository = $pdk->get(PdkOrderRepositoryInterface::class);

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

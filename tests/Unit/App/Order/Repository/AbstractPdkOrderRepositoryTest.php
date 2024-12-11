<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Psr\Log\LoggerInterface;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('gets a single order', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $repository */
    $repository = Pdk::get(PdkOrderRepositoryInterface::class);
    $order      = $repository->get(3);

    expect($order)->toBeInstanceOf(PdkOrder::class);
});

it('gets multiple orders', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $repository */
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
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockPdkOrderRepository $repository */
    $repository = Pdk::get(PdkOrderRepositoryInterface::class);
    $order      = new PdkOrder(['externalIdentifier' => 'PS-123']);
    $newOrder   = $repository->update($order);

    expect($newOrder)->toBeInstanceOf(PdkOrder::class);
});

it('gets order by apiIdentifier', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);
    class MockPdkOrderRepository extends AbstractPdkOrderRepository
    {
        public function get($input): PdkOrder
        {
            return new PdkOrder();
        }
    }
    $repository = new MockPdkOrderRepository(Pdk::get(StorageInterface::class));
    $order      = $repository->getByApiIdentifier('123');

    expect($order)
        ->toBeInstanceOf(PdkOrder::class)
        ->and($logger->getLogs())
        ->toEqual([
                [
                    'level'   => 'notice',
                    'message' => '[PDK]: Implement getByApiIdentifier, in PDK v3 it will be required.',
                    'context' =>
                        [
                            'class'   => 'MyParcelNL\\Pdk\\App\\Order\\Repository\\AbstractPdkOrderRepository',
                        ],
                ],
            ]
        );
});

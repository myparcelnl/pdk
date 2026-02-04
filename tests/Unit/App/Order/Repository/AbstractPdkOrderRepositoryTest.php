<?php

/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Exception\ModelNotFoundException;
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

it('gets order by api identifier', function () {
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
        ->toContain(
            [
                'level'   => 'notice',
                'message' => '[PDK]: Implement getByApiIdentifier, in PDK v3 it will be required.',
                'context' =>
                [
                    'class'   => 'MyParcelNL\\Pdk\\App\\Order\\Repository\\AbstractPdkOrderRepository',
                ],
            ],
        );
});

it('find() uses get() but logs a notice it should be implemented', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);
    class FindMockPdkOrderRepository extends AbstractPdkOrderRepository
    {
        public function get($input): PdkOrder
        {
            return new PdkOrder(['externalIdentifier' => $input]);
        }
    }
    $repository = new FindMockPdkOrderRepository(Pdk::get(StorageInterface::class));
    $order      = $repository->find('123');

    expect($order)
        ->toBeInstanceOf(PdkOrder::class)
        ->and($order->externalIdentifier)
        ->toBe('123')
        ->and($logger->getLogs())
        ->toContain(
            [
                'level'   => 'notice',
                'message' => '[PDK]: Please implement find() in MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository to retrieve orders by their identifier.',
                'context' =>
                [
                    'class' => 'MyParcelNL\\Pdk\\App\\Order\\Repository\\AbstractPdkOrderRepository',
                ],
            ],
        );
});

it('exists() uses find() and returns false if find() returns null', function () {
    class ExistsNullMockPdkOrderRepository extends AbstractPdkOrderRepository
    {
        public function get($input): PdkOrder
        {
            return new PdkOrder();
        }

        public function find($id): ?PdkOrder
        {
            return null;
        }
    }
    $repository = new ExistsNullMockPdkOrderRepository(Pdk::get(StorageInterface::class));

    expect($repository->exists('999'))->toBeFalse();
});

it('exists() uses find() and returns true if find() returns an order', function () {
    class ExistsTrueMockPdkOrderRepository extends AbstractPdkOrderRepository
    {
        public function get($input): PdkOrder
        {
            return new PdkOrder();
        }

        public function find($id): ?PdkOrder
        {
            return new PdkOrder(['externalIdentifier' => $id]);
        }
    }
    $repository = new ExistsTrueMockPdkOrderRepository(Pdk::get(StorageInterface::class));

    expect($repository->exists('123'))->toBeTrue();
});

it('findAll() uses getMany() but logs a notice it should be implemented', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);
    class FindAllMockPdkOrderRepository extends AbstractPdkOrderRepository
    {
        public function get($input): PdkOrder
        {
            return new PdkOrder(['externalIdentifier' => $input]);
        }
    }
    $repository = new FindAllMockPdkOrderRepository(Pdk::get(StorageInterface::class));
    $orders     = $repository->findAll(['1', '2', '3']);

    expect($orders)
        ->toBeInstanceOf(PdkOrderCollection::class)
        ->and($orders->count())
        ->toBe(3)
        ->and($logger->getLogs())
        ->toContain(
            [
                'level'   => 'notice',
                'message' => '[PDK]: Please implement findAll() in MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository to retrieve orders by their identifier.',
                'context' =>
                [
                    'class' => 'MyParcelNL\\Pdk\\App\\Order\\Repository\\AbstractPdkOrderRepository',
                ],
            ],
        );
});

it('findOrFail() always throws ModelNotFoundException unless implemented by a class', function () {
    class FindOrFailMockPdkOrderRepository extends AbstractPdkOrderRepository
    {
        public function get($input): PdkOrder
        {
            return new PdkOrder();
        }
    }
    $repository = new FindOrFailMockPdkOrderRepository(Pdk::get(StorageInterface::class));

    $repository->findOrFail('123');
})->throws(ModelNotFoundException::class);

it('all() returns empty collection and logs a notice it should be implemented', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);
    class AllMockPdkOrderRepository extends AbstractPdkOrderRepository
    {
        public function get($input): PdkOrder
        {
            return new PdkOrder();
        }
    }
    $repository = new AllMockPdkOrderRepository(Pdk::get(StorageInterface::class));
    $orders     = $repository->all();

    expect($orders)
        ->toBeInstanceOf(PdkOrderCollection::class)
        ->and($orders->count())
        ->toBe(0)
        ->and($logger->getLogs())
        ->toContain(
            [
                'level'   => 'notice',
                'message' => '[PDK]: Please implement all() in MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderRepository to retrieve all orders.',
                'context' =>
                [
                    'class' => 'MyParcelNL\\Pdk\\App\\Order\\Repository\\AbstractPdkOrderRepository',
                ],
            ],
        );
});

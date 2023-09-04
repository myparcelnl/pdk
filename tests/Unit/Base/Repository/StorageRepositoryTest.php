<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Base\Repository;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockStorageRepository;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesEachMockPdkInstance());

it('gets data from cache after loading it from storage once', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorageDriver $cache */
    $cache = Pdk::get(CacheStorageInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorageDriver $storage */
    $storage = Pdk::get(StorageDriverInterface::class);
    /** @var MockStorageRepository $repository */
    $repository = Pdk::get(MockStorageRepository::class);

    $repository->getData();

    expect($cache->getReads())
        ->toHaveLength(1)
        ->and($storage->getReads())
        ->toHaveLength(1);

    $repository->getData();

    expect($cache->getReads())
        ->toHaveLength(2)
        ->and($storage->getReads())
        ->toHaveLength(1);

    $repository->getData();
});

it('gets data from additional callback if not present in the cache nor the storage', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorageDriver $cache */
    $cache = Pdk::get(CacheStorageInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorageDriver $storage */
    $storage = Pdk::get(StorageDriverInterface::class);
    /** @var MockStorageRepository $repository */
    $repository = Pdk::get(MockStorageRepository::class);

    $result = $repository->getNonexistentWithFallback();

    expect($cache->getReads())
        ->toHaveLength(1)
        ->and($storage->getReads())
        ->toHaveLength(1)
        ->and($repository->getFallbackCalls())
        ->toBe(1)
        ->and($result)
        ->toEqual('fallback');

    $repository->getNonexistentWithFallback();

    expect($cache->getReads())
        ->toHaveLength(2)
        ->and($storage->getReads())
        ->toHaveLength(1)
        ->and($repository->getFallbackCalls())
        ->toBe(1);
});

it('writes fallback data to storage', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorageDriver $cache */
    $cache = Pdk::get(CacheStorageInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorageDriver $storage */
    $storage = Pdk::get(StorageDriverInterface::class);
    /** @var MockStorageRepository $repository */
    $repository = Pdk::get(MockStorageRepository::class);

    $repository->getNonexistentWithFallback();

    expect($cache->getWrites())
        ->toHaveLength(1)
        ->and($storage->getWrites())
        ->toHaveLength(1)
        ->and($repository->getFallbackCalls())
        ->toBe(1);

    $cache->clear();

    $repository->getNonexistentWithFallback();

    expect($cache->getReads())
        ->toHaveLength(1)
        ->and($storage->getReads())
        ->toHaveLength(2)
        ->and($repository->getFallbackCalls())
        ->toBe(1);
});

it('writes data to storage if it has changed', function ($input) {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorageDriver $cache */
    $cache = Pdk::get(CacheStorageInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorageDriver $storage */
    $storage = Pdk::get(StorageDriverInterface::class);
    /** @var MockStorageRepository $repository */
    $repository = Pdk::get(MockStorageRepository::class);

    $repository->setData($input);

    expect($cache->getWrites())
        ->toHaveLength(1)
        ->and($storage->getWrites())
        ->toHaveLength(1)
        ->and($repository->getData())
        ->toEqual($input);

    $repository->setData($input);

    expect($cache->getWrites())
        ->toHaveLength(1)
        ->and($storage->getWrites())
        ->toHaveLength(1);

    $repository->setData('something else');

    expect($cache->getWrites())
        ->toHaveLength(2)
        ->and($storage->getWrites())
        ->toHaveLength(2)
        ->and($repository->getData())
        ->toEqual('something else');
})->with([
    'string'       => ['whatever'],
    'array'        => [['whatever']],
    'object'       => [(object) ['whatever']],
    'complex data' => function () {
        return [
            [
                new class {
                    public $whatever = 'whatever';
                },
            ],
        ];
    },
]);

it('deletes data from storage', function () {
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorageDriver $cache */
    $cache = Pdk::get(CacheStorageInterface::class);
    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockMemoryCacheStorageDriver $storage */
    $storage = Pdk::get(StorageDriverInterface::class);
    /** @var MockStorageRepository $repository */
    $repository = Pdk::get(MockStorageRepository::class);

    $repository->setData('whatever');

    expect($cache->getWrites())
        ->toHaveLength(1)
        ->and($storage->getWrites())
        ->toHaveLength(1)
        ->and($repository->getData())
        ->toEqual('whatever');

    $repository->deleteData();

    expect($cache->getDeletes())
        ->toHaveLength(1)
        ->and($storage->getDeletes())
        ->toHaveLength(1)
        ->and($repository->getData())
        ->toEqual(null);
});

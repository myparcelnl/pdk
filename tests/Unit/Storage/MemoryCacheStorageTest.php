<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('can get and set items', function () {
    /** @var \MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage */
    $storage = Pdk::get(MemoryCacheStorage::class);

    $storage->set('foo', 'bar');

    expect($storage->get('foo'))->toBe('bar');
});

it('can delete items', function () {
    /** @var \MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage */
    $storage = Pdk::get(MemoryCacheStorage::class);

    $storage->set('foo', 'bar');
    $storage->delete('foo');

    expect($storage->has('foo'))->toBeFalse();
});

<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

it('can get and set items', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage */
    $storage = $pdk->get(MemoryCacheStorage::class);

    $storage->set('foo', 'bar');

    expect($storage->get('foo'))->toBe('bar');
});

it('can delete items', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());
    /** @var \MyParcelNL\Pdk\Storage\MemoryCacheStorage $storage */
    $storage = $pdk->get(MemoryCacheStorage::class);

    $storage->set('foo', 'bar');
    $storage->delete('foo');

    expect($storage->has('foo'))->toBeFalse();
});

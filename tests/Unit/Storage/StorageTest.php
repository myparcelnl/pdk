<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('gets storage', function () {
    expect(Pdk::get(StorageInterface::class))->toBeInstanceOf(StorageInterface::class);
});

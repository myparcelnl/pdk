<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

it('gets storage', function () {
    expect(Pdk::get(StorageInterface::class))->toBeInstanceOf(StorageInterface::class);
});

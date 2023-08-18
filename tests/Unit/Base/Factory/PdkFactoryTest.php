<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Factory;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

it('can create a pdk instance', function () {
    expect(Pdk::get(StorageInterface::class))
        ->toBeInstanceOf(StorageInterface::class);
});

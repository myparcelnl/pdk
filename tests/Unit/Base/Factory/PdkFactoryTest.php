<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Storage\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

it('can create a pdk instance', function () {
    $pdk = PdkFactory::create(MockPdkConfig::create());

    expect($pdk->get(StorageInterface::class))
        ->toBeInstanceOf(StorageInterface::class);
});

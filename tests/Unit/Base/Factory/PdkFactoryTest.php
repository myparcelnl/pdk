<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Storage\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\Config;
use MyParcelNL\Pdk\Tests\Bootstrap\MockStorage;

it('successfully creates pdk instance', function () {
    $pdk = PdkFactory::createPdk(Config::provideDefaultPdkConfig());

    expect($pdk->get('storage.default'))
        ->toBeInstanceOf(StorageInterface::class)
        ->and($pdk->has('storage.default'))
        ->toBeTrue();
});

it('throws error when configuration is empty', function () {
    PdkFactory::createPdk([]);
})->throws(InvalidArgumentException::class);

it('throws error when configuration is not valid', function () {
    PdkFactory::createPdk(['api' => new MockStorage()]);
})->throws(InvalidArgumentException::class);


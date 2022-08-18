<?php
/** @noinspection PhpUndefinedMethodInspection,StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use DI\NotFoundException;
use MyParcelNL\Pdk\Base\Exception\InvalidFacadeException;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

it('can call instance behind facade', function () {
    PdkFactory::create(MockPdkConfig::create());

    expect(Pdk::has(StorageInterface::class))->toEqual(true);
});

it('throws error if facade points to nonexistent container item', function () {
    class InvalidMock extends Facade
    {
        /** @noinspection PhpUnused */
        protected static function getFacadeAccessor(): string
        {
            return 'nonexistent.property';
        }
    }

    PdkFactory::create(MockPdkConfig::create());

    InvalidMock::method();
})->throws(NotFoundException::class);

it('throws error when facade accessor is not set', function () {
    // We're not calling PdkFactory::create();
    Pdk::get(StorageInterface::class);
})->throws(InvalidFacadeException::class);

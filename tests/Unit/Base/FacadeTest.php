<?php
/** @noinspection PhpUndefinedMethodInspection,StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use DI\NotFoundException;
use MyParcelNL\Pdk\Base\Exception\InvalidFacadeException;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('can call instance behind facade', function () {
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

    InvalidMock::method();
})->throws(NotFoundException::class);

it('throws error when facade accessor is not set', function () {
    Facade::setPdkInstance(null);

    Pdk::get(StorageInterface::class);
})->throws(InvalidFacadeException::class);

<?php
/** @noinspection AutoloadingIssuesInspection,PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection,PhpIllegalPsrClassPathInspection,PhpMultipleClassesDeclarationsInOneFile */

declare(strict_types=1);

use DI\NotFoundException;
use MyParcelNL\Pdk\Base\Exception\InvalidFacadeException;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Storage;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

it('can call instance behind facade', function () {
    PdkFactory::create(MockPdkConfig::DEFAULT_CONFIG);

    expect(Storage::has('key'))->toEqual(false);
});

it('throws error if facade points to nonexistent container item', function () {
    class InvalidMock extends Facade
    {
        protected static function getFacadeAccessor(): string
        {
            return 'nonexistent.property';
        }
    }

    PdkFactory::create(MockPdkConfig::DEFAULT_CONFIG);

    InvalidMock::method();
})->throws(NotFoundException::class);

it('throws error when facade accessor is not set', function () {
    // We're not calling PdkFactory::create();
    Storage::get('item');
})->throws(InvalidFacadeException::class);

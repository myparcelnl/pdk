<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Exception\PdkConfigException;
use function DI\value;
use function MyParcelNL\Pdk\Tests\mockPdkProperties;

beforeAll(function () {
    //    MockPdkFactory::clear();
});

afterEach(function () {
    //    MockPdkFactory::clear();
});

afterAll(function () {
});

it('works', function () {
    expect(Pdk::get(ApiServiceInterface::class))->toBeInstanceOf(
        ApiServiceInterface::class
    );
});

it('exposes mode property', function (string $mode, bool $isDevelopment) {
    mockPdkProperties(['mode' => value($mode)]);

    expect(Pdk::getMode())
        ->toBe($mode)
        ->and(Pdk::isDevelopment())
        ->toBe($isDevelopment)
        ->and(Pdk::isProduction())
        ->toBe(! $isDevelopment);
})->with([
    'production'  => [
        'mode'          => \MyParcelNL\Pdk\Base\Pdk::MODE_PRODUCTION,
        'isDevelopment' => false,
    ],
    'development' => [
        'mode'          => \MyParcelNL\Pdk\Base\Pdk::MODE_DEVELOPMENT,
        'isDevelopment' => true,
    ],
]);

it('throws error if appInfo is missing', function () {
    Pdk::getAppInfo();
})->throws(PdkConfigException::class);

it('throws error if appInfo is not an instance of AppInfo', function () {
    mockPdkProperties(['appInfo' => value('foo')]);

    Pdk::getAppInfo();
})->throws(PdkConfigException::class);

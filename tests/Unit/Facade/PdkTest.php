<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Exception\PdkConfigException;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function DI\value;

afterEach(function () {
    Pdk::setPdkInstance(null);
});

it('works', function () {
    PdkFactory::create(MockPdkConfig::create());

    expect(Pdk::get(ApiServiceInterface::class))->toBeInstanceOf(
        ApiServiceInterface::class
    );
});

it('exposes mode property', function (string $mode, bool $isDevelopment) {
    PdkFactory::create(MockPdkConfig::create(['mode' => value($mode)]));

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
    PdkFactory::create();

    Pdk::getAppInfo();
})->throws(PdkConfigException::class);

it('throws error if appInfo is not an instance of AppInfo', function () {
    PdkFactory::create(MockPdkConfig::create(['appInfo' => value('foo')]));

    Pdk::getAppInfo();
})->throws(PdkConfigException::class);

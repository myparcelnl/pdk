<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use function DI\value;

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

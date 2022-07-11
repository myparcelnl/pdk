<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Storage\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\Config;
use MyParcelNL\Pdk\Tests\Bootstrap\MockStorage;

it('can create a pdk instance', function (array $config) {
    $pdk = PdkFactory::create($config);

    expect($pdk->get('storage.default'))
        ->toBeInstanceOf(StorageInterface::class)
        ->and($pdk->has('storage.default'))
        ->toBeTrue();
})->with([
    'default config'                  => [Config::provideDefaultPdkConfig()],
    'classes instead of class string' => [['storage' => ['default' => new MockStorage()]]],
]);

it('throws errors', function ($configuration) {
    PdkFactory::create($configuration);
})
    ->throws(InvalidArgumentException::class)
    ->with([
        'empty configuration '  => [[]],
        'invalid configuration' => [['api' => MockStorage::class]],
    ]);

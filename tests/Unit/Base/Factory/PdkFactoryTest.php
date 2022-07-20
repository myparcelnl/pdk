<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Storage\StorageInterface;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;
use MyParcelNL\Pdk\Tests\Bootstrap\MockLogger;
use MyParcelNL\Pdk\Tests\Bootstrap\MockStorage;

it('can create a pdk instance', function (array $config) {
    $pdk = PdkFactory::create($config);

    expect($pdk->get('storage.default'))
        ->toBeInstanceOf(StorageInterface::class)
        ->and($pdk->has('storage.default'))
        ->toBeTrue();
})->with([
    'default config'                  => [MockConfig::DEFAULT_CONFIG],
    'classes instead of class string' => [
        [
            'storage' => [
                'default' => new MockStorage(),
            ],
            'logger'  => [
                'default' => new MockLogger(),
            ],
        ],
    ],
]);

it('throws errors', function ($configuration) {
    PdkFactory::create($configuration);
})
    ->throws(InvalidArgumentException::class)
    ->with([
        'empty configuration '  => [[]],
        'invalid configuration' => [['api' => MockStorage::class]],
    ]);

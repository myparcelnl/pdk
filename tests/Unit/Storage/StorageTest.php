<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\MockConfig;

it('gets storage', function () {
    $pdk = PdkFactory::create(MockConfig::DEFAULT_CONFIG);

    expect($pdk->get('storage.default'))->toBeInstanceOf(StorageInterface::class);
});

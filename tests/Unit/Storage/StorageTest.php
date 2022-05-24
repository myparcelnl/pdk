<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Tests\Bootstrap\Config;
use const Throwable;

it('gets storage', function () {
    $pdk = PdkFactory::createPdk(Config::provideDefaultPdkConfig());

    expect($pdk->get('storage.default'))->toBeInstanceOf(StorageInterface::class);
});

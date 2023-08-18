<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkFactory;

require 'vendor/autoload.php';

$start = microtime(true);

for ($i = 0; $i < 10000; $i++) {
    MockPdkFactory::create();
}

$milliseconds = (microtime(true) - $start) * 1000;

/** @noinspection ForgottenDebugOutputInspection */
error_log("Time taken: $milliseconds ms");

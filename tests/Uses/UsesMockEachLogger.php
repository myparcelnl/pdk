<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Facade\Pdk;
use Psr\Log\LoggerInterface;

final class UsesMockEachLogger implements BaseMock
{
    public function afterEach(): void
    {
        /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
        $logger = Pdk::get(LoggerInterface::class);

        $logger->clear();
    }
}

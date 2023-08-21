<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Storage\MemoryCacheStorage;

final class MockMemoryCacheStorage extends MemoryCacheStorage
{
    public function reset(): void
    {
        $this->data = [];
    }
}

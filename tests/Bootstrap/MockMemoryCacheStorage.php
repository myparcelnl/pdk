<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use Symfony\Contracts\Service\ResetInterface;

final class MockMemoryCacheStorage extends MemoryCacheStorage implements ResetInterface
{
    public function reset(): void
    {
        $this->data = [];
    }
}

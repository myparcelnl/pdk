<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Storage;

use MyParcelNL\Pdk\Console\Storage\Contract\ConsoleStorageInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;

final class ConsoleMemoryCacheStorage extends MemoryCacheStorage implements ConsoleStorageInterface
{
    public function clear(): void
    {
        $this->data = [];
    }
}

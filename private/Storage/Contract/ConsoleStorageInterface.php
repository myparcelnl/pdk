<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Storage\Contract;

use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

interface ConsoleStorageInterface extends StorageInterface
{
    public function clear(): void;
}

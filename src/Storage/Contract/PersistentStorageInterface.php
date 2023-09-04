<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage\Contract;

/**
 * For persistent storages where we should minimize the amount of reads and writes.
 */
interface PersistentStorageInterface extends StorageDriverInterface
{
    public function persist(): void;
}

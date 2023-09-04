<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage\Contract;

/**
 * For in-memory storages.
 */
interface MemoryStorageInterface extends StorageDriverInterface
{
    /**
     * Clear all items from the storage.
     */
    public function clear(): void;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage\Contract;

interface StorageDriverInterface extends ReadOnlyStorageDriverInterface
{
    /**
     * Delete an item from the storage.
     */
    public function delete(string $storageKey): void;

    /**
     * Store an item in the storage.
     */
    public function put(string $storageKey, $value): void;
}

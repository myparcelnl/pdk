<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage\Contract;

interface WritableStorageInterface extends ReadOnlyStorageInterface
{
    /**
     * Delete an item from the storage.
     */
    public function delete(string $storageKey): void;

    /**
     * Store an item in the storage.
     */
    public function set(string $storageKey, $value): void;
}

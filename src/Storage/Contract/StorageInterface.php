<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage\Contract;

interface StorageInterface
{
    /**
     * Delete an item from the storage.
     */
    public function delete(string $storageKey): void;

    /**
     * Retrieve an item from the storage.
     *
     * @return mixed
     */
    public function get(string $storageKey);

    /**
     * Check if an item exists in the storage.
     */
    public function has(string $storageKey): bool;

    /**
     * Store an item in the storage.
     */
    public function set(string $storageKey, $item): void;
}

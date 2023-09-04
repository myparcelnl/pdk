<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage\Contract;

interface ReadOnlyStorageDriverInterface
{
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
}

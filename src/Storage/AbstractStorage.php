<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

abstract class AbstractStorage implements StorageInterface
{
    /**
     * @param  string $storageKey
     *
     * @return bool
     */
    public function has(string $storageKey): bool
    {
        return (bool) $this->get($storageKey);
    }
}

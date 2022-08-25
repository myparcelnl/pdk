<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

interface StorageInterface
{
    /**
     * @param  string $storageKey
     *
     * @return void
     */
    public function delete(string $storageKey): void;

    /**
     * @param  string $storageKey
     *
     * @return mixed
     */
    public function get(string $storageKey);

    /**
     * @param  string $storageKey
     *
     * @return bool
     */
    public function has(string $storageKey): bool;

    /**
     * @param  string $storageKey
     * @param  mixed  $item
     *
     * @return void
     */
    public function set(string $storageKey, $item): void;
}

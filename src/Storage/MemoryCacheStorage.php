<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface;

class MemoryCacheStorage implements CacheStorageInterface
{
    protected $data = [];

    /**
     * @param  string $storageKey
     *
     * @return void
     */
    public function delete(string $storageKey): void
    {
        Arr::forget($this->data, $storageKey);
    }

    /**
     * @param  string $storageKey
     *
     * @return mixed
     */
    public function get(string $storageKey)
    {
        return Arr::get($this->data, $storageKey);
    }

    /**
     * @param  string $storageKey
     *
     * @return bool
     */
    public function has(string $storageKey): bool
    {
        return Arr::has($this->data, $storageKey);
    }

    /**
     * @param  string $storageKey
     * @param         $value
     *
     * @return void
     */
    public function set(string $storageKey, $value): void
    {
        Arr::set($this->data, $storageKey, $value);
    }
}

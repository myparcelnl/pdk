<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Storage\AbstractStorage;
use MyParcelNL\Sdk\src\Support\Arr;

class MockStorage extends AbstractStorage
{
    private $data = [];

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
     * @param         $item
     *
     * @return void
     */
    public function set(string $storageKey, $item): void
    {
        Arr::set($this->data, $storageKey, $item);
    }
}

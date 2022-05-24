<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Storage\AbstractStorage;
use MyParcelNL\Sdk\src\Support\Arr;

class MockStorage extends AbstractStorage
{
    private static $data = [];

    public function delete(string $storageKey): void
    {
        Arr::forget(self::$data, $storageKey);
    }

    /**
     * @param  string $storageKey
     * @param  bool   $skipCache
     *
     * @return mixed
     */
    public function get(string $storageKey, bool $skipCache = false)
    {
        return Arr::get(self::$data, $storageKey);
    }

    /**
     * @param  string $storageKey
     * @param         $item
     *
     * @return void
     */
    public function set(string $storageKey, $item): void
    {
        Arr::set(self::$data, $storageKey, $item);
    }
}

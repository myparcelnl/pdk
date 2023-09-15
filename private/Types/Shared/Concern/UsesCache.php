<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Concern;

use MyParcelNL\Pdk\Console\Types\Storage\CacheFileStorage;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;
use MyParcelNL\Sdk\src\Support\Str;

trait UsesCache
{
    /**
     * @var \MyParcelNL\Pdk\Console\Types\Storage\CacheFileStorage
     */
    private static $cacheFileStorage;

    /**
     * @var \MyParcelNL\Pdk\Storage\MemoryCacheStorage
     */
    private static $memoryCache;

    /**
     * @return mixed
     */
    protected function cache(string $key, callable $callback, string $driverName = 'memory')
    {
        $driver = $this->getDriver($driverName);

        if ($driver->has($key)) {
            $contents = $driver->get($key);

            return $this->tryUnserialize($contents);
        }

        $result = $callback();

        $driver->set($key, $result);

        return $result;
    }

    /**
     * @return mixed
     */
    protected function fileCache(string $key, callable $callback)
    {
        return $this->cache($key, fn() => $this->cache($key, $callback, 'file'));
    }

    private function getDriver(string $driverName): StorageInterface
    {
        switch ($driverName) {
            case 'file':
                self::$cacheFileStorage ??= Pdk::get(CacheFileStorage::class);
                $driver                 = self::$cacheFileStorage;
                break;

            default:
                self::$memoryCache ??= Pdk::get(MemoryCacheStorage::class);
                $driver            = self::$memoryCache;
                break;
        }

        return $driver;
    }

    /**
     * @return mixed|string
     */
    private function tryUnserialize(mixed $contents)
    {
        if (is_string($contents) && Str::startsWith($contents, 'a:')) {
            $contents = unserialize($contents, ['allowed_classes' => true]);
        }

        return $contents;
    }
}

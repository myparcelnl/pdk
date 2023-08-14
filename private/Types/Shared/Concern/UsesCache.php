<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Concern;

use MyParcelNL\Pdk\Console\Types\Storage\CacheFileStorage;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use MyParcelNL\Pdk\Storage\MemoryCacheStorage;

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
     * @param  string   $key
     * @param  callable $callback
     * @param  string   $driverName
     *
     * @return mixed
     */
    protected function cache(string $key, callable $callback, string $driverName = 'memory')
    {
        $driver = $this->getDriver($driverName);

        if ($driver->has($key)) {
            return $driver->get($key);
        }

        $res = $callback();

        $driver->set($key, $res);

        return $res;
    }

    /**
     * @param  string $driverName
     *
     * @return \MyParcelNL\Pdk\Storage\Contract\StorageInterface
     */
    private function getDriver(string $driverName): StorageInterface
    {
        switch ($driverName) {
            case 'file':
                self::$cacheFileStorage = self::$cacheFileStorage ?? new CacheFileStorage();
                $driver                 = self::$cacheFileStorage;
                break;

            default:
                self::$memoryCache = self::$memoryCache ?? new MemoryCacheStorage();
                $driver            = self::$memoryCache;
                break;
        }

        return $driver;
    }
}

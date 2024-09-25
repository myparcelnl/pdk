<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Concern;

use InvalidArgumentException;
use MyParcelNL\Pdk\Console\Storage\ConsoleFileCacheStorage;
use MyParcelNL\Pdk\Console\Storage\ConsoleMemoryCacheStorage;
use MyParcelNL\Pdk\Console\Storage\Contract\ConsoleStorageInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Sdk\src\Support\Str;

trait UsesCache
{
    /**
     * @var class-string<ConsoleStorageInterface>[]
     */
    private static $caches = [];

    /**
     * @var bool
     */
    protected $readsFromCache = true;

    /**
     * @var bool
     */
    protected $writesToCache = true;

    /**
     * @param  string                                $key
     * @param  callable                              $callback
     * @param  class-string<ConsoleStorageInterface> $driverClass
     *
     * @return mixed
     */
    protected function cache(string $key, callable $callback, string $driverClass = ConsoleMemoryCacheStorage::class)
    {
        $driver = $this->getDriver($driverClass);

        if ($this->readsFromCache && $driver->has($key)) {
            return $this->tryUnserialize($driver->get($key));
        }

        $result = $callback();

        if ($this->writesToCache) {
            $driver->set($key, $result);
        }

        return $result;
    }

    /**
     * @return void
     */
    protected function clearCache(): void
    {
        foreach (self::$caches as $cache) {
            $this
                ->getDriver($cache)
                ->clear();
        }
    }

    /**
     * @param  string   $key
     * @param  callable $callback
     *
     * @return mixed
     */
    protected function fileCache(string $key, callable $callback)
    {
        return $this->cache($key, function () use ($key, $callback) {
            return $this->cache($key, $callback, ConsoleFileCacheStorage::class);
        });
    }

    /**
     * @template T of ConsoleStorageInterface
     * @param  class-string<T> $driverClass
     *
     * @return T
     */
    private function getDriver(string $driverClass): ConsoleStorageInterface
    {
        $driver = Pdk::get($driverClass);

        if (! $driver instanceof ConsoleStorageInterface) {
            throw new InvalidArgumentException(
                sprintf('Driver %s must implement %s', $driverClass, ConsoleStorageInterface::class)
            );
        }

        if (! in_array($driverClass, self::$caches, true)) {
            self::$caches[] = $driverClass;
        }

        return $driver;
    }

    /**
     * @param  mixed $contents
     *
     * @return mixed|string
     */
    private function tryUnserialize($contents)
    {
        if (is_string($contents) && Str::startsWith($contents, 'a:')) {
            $contents = unserialize($contents, ['allowed_classes' => true]);
        }

        return $contents;
    }
}

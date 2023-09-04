<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;

class StorageRepository extends Repository
{
    /**
     * @var \MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface
     */
    protected $storage;

    /**
     * @var array{string, string}
     */
    protected $storageHashMap = [];

    //    /**
    //     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageStackInterface $stack
    //     */
    //    public function __construct(StorageStackInterface $stack)
    //    {
    //
    //    }
    //    /**
    //     * @param  \MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface $cache
    //     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface      $storage
    //     */
    //    public function __construct(CacheStorageInterface $cache, StorageDriverInterface $storage)
    //    {
    //        parent::__construct($cache);
    //        $this->storage = $storage;
    //    }

    /**
     * @param  string $key
     *
     * @return void
     */
    protected function delete(string $key): void
    {
        $fullKey = $this->getKeyPrefix() . $key;

        $this->storage->delete($fullKey);
        $this->cache->delete($fullKey);
        unset($this->storageHashMap[$fullKey]);
    }

    /**
     * @param  mixed $data
     *
     * @return null|string
     */
    protected function generateDataHash($data): ?string
    {
        return Utils::generateHash($data);
    }

    /**
     * Try to get the value from cache, if not found, try to get it from storage.
     *
     * @param  string                                                       $key
     * @param  null|callable                                                $callback
     * @param  bool                                                         $skipCache
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface|null $storage
     *
     * @return mixed
     */
    protected function retrieve(
        string                 $key,
        ?callable              $callback = null,
        bool                   $skipCache = false,
        StorageDriverInterface $storage = null
    ) {
        if ($storage) {
            return parent::retrieve($key, $callback, $skipCache, $storage);
        }

        return parent::retrieve($key, function () use ($key, $callback, $skipCache) {
            $storageKey = Pdk::get('createSettingsStorageKey')($key);

            return $this->transformData($key, parent::retrieve($storageKey, $callback, $skipCache, $this->storage));
        }, $skipCache, $storage);
    }

    /**
     * @param  string $key
     * @param         $data
     *
     * @return mixed
     */
    protected function save(string $key, $data)
    {
        $fullKey = $this->getKeyPrefix() . $key;

        $existingHash = $this->storageHashMap[$fullKey] ?? null;
        $newHash      = $this->generateDataHash($data);

        if ($existingHash === $newHash) {
            return $data;
        }

        $this->storage->put($fullKey, $data);

        $this->storageHashMap[$fullKey] = $newHash;

        return parent::save($key, $data);
    }

    /**
     * @param  string $key
     * @param  mixed  $data
     *
     * @return mixed
     */
    protected function transformData(string $key, $data)
    {
        return $data;
    }
}

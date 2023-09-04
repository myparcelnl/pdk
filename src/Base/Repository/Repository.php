<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Storage\Contract\MemoryStorageInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;

class Repository
{
    /**
     * @var \MyParcelNL\Pdk\Storage\Contract\MemoryStorageInterface
     */
    protected $cache;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\MemoryStorageInterface $memoryStorage
     */
    public function __construct(MemoryStorageInterface $memoryStorage)
    {
        $this->cache = $memoryStorage;
    }

    /**
     * @return string
     */
    protected function getKeyPrefix(): string
    {
        return '';
    }

    /**
     * @param  string                                                       $key
     * @param  null|callable                                                $callback
     * @param  bool                                                         $skipCache
     * @param  null|\MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface $storage
     *
     * @return mixed
     */
    protected function retrieve(
        string                 $key,
        ?callable              $callback = null,
        bool                   $skipCache = false,
        StorageDriverInterface $storage = null
    ) {
        $storage = $storage ?? $this->cache;

        $fullKey = $this->getKeyPrefix() . $key;

        if (null !== $callback && ($skipCache || ! $storage->has($fullKey))) {
            $value = $callback();

            $storage->put($fullKey, $value);
        } else {
            $value = $storage->get($fullKey);
        }

        return is_object($value) ? clone $value : $value;
    }

    /**
     * @param  string $key
     * @param  mixed  $data
     *
     * @return mixed
     */
    protected function save(string $key, $data)
    {
        $this->cache->put($this->getKeyPrefix() . $key, $data);

        return $data;
    }
}

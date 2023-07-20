<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

class Repository
{
    /**
     * @var \MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface
     */
    protected $cache;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\CacheStorageInterface $cache
     */
    public function __construct(CacheStorageInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    protected function getAllStorageKey(): string
    {
        return 'all';
    }

    /**
     * @return string
     */
    protected function getKeyPrefix(): string
    {
        return '';
    }

    /**
     * @param  string                                                 $key
     * @param  null|callable                                          $callback
     * @param  bool                                                   $force
     * @param  null|\MyParcelNL\Pdk\Storage\Contract\StorageInterface $storage
     *
     * @return mixed
     */
    protected function retrieve(
        string           $key,
        ?callable        $callback = null,
        bool             $force = false,
        StorageInterface $storage = null
    ) {
        $storage = $storage ?? $this->cache;

        $fullKey = $this->getKeyPrefix() . $key;

        if (null !== $callback && ($force || ! $storage->has($fullKey))) {
            $data = $callback();

            $storage->set($fullKey, $data);
        }

        $value = $storage->get($fullKey);

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
        $this->cache->set($this->getKeyPrefix() . $key, $data);

        return $data;
    }
}

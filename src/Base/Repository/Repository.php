<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Base\Contract\RepositoryInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

class Repository implements RepositoryInterface
{
    /**
     * @var \MyParcelNL\Pdk\Storage\Contract\StorageInterface
     */
    protected $storage;

    /**
     * @var array<string, string>
     */
    protected $storageHashMap = [];

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return void
     */
    public function persist(): void
    {
        $prefix = $this->getKeyPrefix();

        foreach ($this->storageHashMap as $key => $hash) {
            $fullKey = $prefix . $key;
            $data    = $this->storage->get($fullKey);

            if ($hash === $this->generateDataHash($data)) {
                continue;
            }

            $this->storage->set($fullKey, $data);
            $this->storageHashMap[$fullKey] = $this->generateDataHash($data);
        }
    }

    /**
     * @param  string        $key
     * @param  null|callable $callback
     * @param  bool          $force
     *
     * @return mixed
     */
    public function retrieve(string $key, ?callable $callback = null, bool $force = false)
    {
        $fullKey = $this->getKeyPrefix() . $key;

        if (null !== $callback && ($force || ! $this->storage->has($fullKey))) {
            $data = $callback();

            $this->storage->set($fullKey, $data);
        }

        $value = $this->storage->get($fullKey);

        return is_object($value) ? clone $value : $value;
    }

    /**
     * @param  string $key
     * @param  mixed  $data
     *
     * @return mixed
     */
    public function save(string $key, $data)
    {
        $this->storage->set($this->getKeyPrefix() . $key, $data);
        $this->storage->delete($this->getKeyPrefix() . $this->getAllStorageKey());

        return $data;
    }

    /**
     * @param  mixed $data
     *
     * @return null|string
     */
    protected function generateDataHash($data): ?string
    {
        if (! $data) {
            return null;
        }

        return md5(var_export($data, true));
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
     * @param  callable $callback
     *
     * @return mixed
     */
    protected function retrieveAll(callable $callback)
    {
        return $this->retrieve($this->getAllStorageKey(), $callback);
    }
}

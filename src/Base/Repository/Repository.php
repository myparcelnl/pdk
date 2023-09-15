<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

class Repository
{
    /**
     * @var \MyParcelNL\Pdk\Storage\Contract\StorageInterface
     */
    protected $storage;

    /**
     * @var array{string, string}
     */
    protected $storageHashMap = [];

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

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
     * @param  null|callable $callback
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
     * @return mixed
     */
    public function save(string $key, mixed $data)
    {
        $this->storage->set($this->getKeyPrefix() . $key, $data);
        $this->storage->delete($this->getKeyPrefix() . $this->getAllStorageKey());

        return $data;
    }

    protected function generateDataHash(mixed $data): ?string
    {
        if (! $data) {
            return null;
        }

        return md5(var_export($data, true));
    }

    protected function getAllStorageKey(): string
    {
        return 'all';
    }

    protected function getKeyPrefix(): string
    {
        return '';
    }

    /**
     * @return mixed
     */
    protected function retrieveAll(callable $callback)
    {
        return $this->retrieve($this->getAllStorageKey(), $callback);
    }
}

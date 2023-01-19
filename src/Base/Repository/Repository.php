<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Storage\StorageInterface;

class Repository
{
    /**
     * @var \MyParcelNL\Pdk\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var array{string, string}
     */
    protected $storageHashMap = [];

    /**
     * @param  \MyParcelNL\Pdk\Storage\StorageInterface $storage
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
     * @param  string   $key
     * @param  callable $callback
     * @param  bool     $force
     *
     * @return mixed
     */
    public function retrieve(string $key, callable $callback, bool $force = false)
    {
        $fullKey = $this->getKeyPrefix() . $key;

        if ($force || ! $this->storage->has($fullKey)) {
            $data = $callback();

            $this->storage->set($fullKey, $data);
        }

        $var = $this->storage->get($fullKey);
        return is_object($var) ? clone $var : $var;
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
    protected function getKeyPrefix(): string
    {
        return '';
    }
}

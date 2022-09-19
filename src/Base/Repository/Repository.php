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
        foreach ($this->storageHashMap as $key => $hash) {
            $data = $this->storage->get($key);

            if ($hash === $this->generateDataHash($data)) {
                continue;
            }

            $this->storage->set($key, $data);
            $this->storageHashMap[$key] = $this->generateDataHash($data);
        }
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
     * @param  string $key
     * @param  mixed  $data
     *
     * @return mixed
     */
    protected function save(string $key, $data)
    {
        $this->storage->set($key, $data);

        return $data;
    }
}

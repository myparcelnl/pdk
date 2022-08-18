<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Storage\StorageInterface;

abstract class AbstractRepository
{
    /**
     * @var \MyParcelNL\Pdk\Api\Service\ApiServiceInterface
     */
    protected $api;

    /**
     * @var \MyParcelNL\Pdk\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var array{string, string}
     */
    protected $storageHashMap = [];

    /**
     * @param  \MyParcelNL\Pdk\Storage\StorageInterface        $storage
     * @param  \MyParcelNL\Pdk\Api\Service\ApiServiceInterface $api
     */
    public function __construct(StorageInterface $storage, ApiServiceInterface $api)
    {
        $this->storage = $storage;
        $this->api     = $api;
    }

    /**
     * @return void
     */
    public function save(): void
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
     * @param  string   $key
     * @param  callable $callback
     *
     * @return mixed
     */
    protected function retrieve(string $key, callable $callback)
    {
        if (! $this->storage->has($key)) {
            $data = $callback();

            $this->storageHashMap[$key] = $this->generateDataHash(null);
            $this->storage->set($key, $data);
        }

        return $data ?? $this->storage->get($key);
    }

    /**
     * @param  mixed $data
     *
     * @return null|string
     */
    private function generateDataHash($data): ?string
    {
        if (! $data) {
            return null;
        }

        return md5(var_export($data, true));
    }
}

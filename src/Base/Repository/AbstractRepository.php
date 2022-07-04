<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Repository;

use MyParcelNL\Pdk\Base\Pdk;

abstract class AbstractRepository
{
    /**
     * @var array{string, string}
     */
    public $storageSetCount = [];

    /**
     * @var \MyParcelNL\Pdk\Api\MyParcelApiService
     */
    protected $api;

    /**
     * @var \MyParcelNL\Pdk\Storage\StorageInterface
     */
    protected $storage;

    /**
     * @var string
     */
    protected $storageDriver = 'default';

    /**
     * @var array{string, string}
     */
    protected $storageHashMap = [];

    /**
     * @param  \MyParcelNL\Pdk\Base\Pdk $pdk
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct(Pdk $pdk)
    {
        $this->storage = $pdk->get("storage.$this->storageDriver");
        $this->api     = $pdk->get('api');
    }

    /**
     * @param  string $key
     *
     * @return int
     */
    public function getStorageSetCount(string $key): int
    {
        return $this->storageSetCount[$key] ?? 0;
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
            $this->storageSetCount[$key] = ($this->storageSetCount[$key] ?? 0) + 1;
            $this->storageHashMap[$key]  = $this->generateDataHash($data);
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

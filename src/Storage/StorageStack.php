<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Storage;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageStackInterface;

/**
 *  layers example: memory cache - file cache - database. minimize database calls
 */
final class StorageStack implements StorageStackInterface
{
    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection
     */
    private $layers;

    /**
     * @param  array<\MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface> $layers
     */
    public function __construct(array $layers)
    {
        $this->layers = new Collection($layers);
    }

    /**
     * @param  string $storageKey
     *
     * @return void
     */
    public function delete(string $storageKey): void
    {
        $this->layers->each(function (StorageDriverInterface $storage) use ($storageKey): void {
            $storage->delete($storageKey);
        });
    }

    /**
     * @param  string $storageKey
     *
     * @return mixed
     */
    public function get(string $storageKey)
    {
        return $this->layers->reduce(function ($carry, StorageDriverInterface $storage) use ($storageKey) {
            return $storage->has($storageKey) ? $storage->get($storageKey) : $carry;
        });
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    public function getLayers(): Collection
    {
        return $this->layers;
    }

    /**
     * @param  string $storageKey
     *
     * @return bool
     */
    public function has(string $storageKey): bool
    {
        return $this->layers->reduce(function (bool $carry, StorageDriverInterface $storage) use ($storageKey): bool {
            return $carry || $storage->has($storageKey);
        }, false);
    }

    /**
     * @param  null|string $layer
     *
     * @return null|\MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface
     */
    public function layer(?string $layer = null): ?StorageDriverInterface
    {
        if (null === $layer) {
            return $this;
        }

        return $this->layers->get($layer);
    }

    /**
     * @param  string $storageKey
     * @param         $value
     *
     * @return void
     */
    public function put(string $storageKey, $value): void
    {
        $this->layers->each(function (StorageDriverInterface $storage) use ($storageKey, $value): void {
            $storage->put($storageKey, $value);
        });
    }
}

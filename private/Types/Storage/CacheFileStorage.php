<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Storage;

use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use RuntimeException;
use Throwable;

final class CacheFileStorage implements StorageInterface
{
    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    private $fileSystem;

    /**
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface $fileSystem
     */
    public function __construct(FileSystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * @param  string $storageKey
     *
     * @return void
     */
    public function delete(string $storageKey): void
    {
        $this->fileSystem->unlink($storageKey);
    }

    /**
     * @param  string $storageKey
     *
     * @return string
     */
    public function get(string $storageKey): string
    {
        return $this->fileSystem->get($storageKey);
    }

    /**
     * @param  string $storageKey
     *
     * @return bool
     */
    public function has(string $storageKey): bool
    {
        return $this->fileSystem->fileExists($storageKey);
    }

    /**
     * @param  string $storageKey
     * @param  mixed  $item
     *
     * @return void
     * @throws \Exception
     */
    public function set(string $storageKey, $item): void
    {
        $this->fileSystem->mkdir($this->fileSystem->dirname($storageKey), true);

        if (! is_scalar($item)) {
            if ($item instanceof StorableArrayable) {
                $item = $item->toStorableArray();
            }

            try {
                $item = serialize($item);
            } catch (Throwable $th) {
                throw new RuntimeException("Error serializing item: {$th->getMessage()}", 1);
            }
        }

        $this->fileSystem->put($storageKey, $item);
    }
}

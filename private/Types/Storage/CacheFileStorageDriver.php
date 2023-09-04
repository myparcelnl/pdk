<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Storage;

use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;
use RuntimeException;
use Throwable;

final class CacheFileStorageDriver implements StorageDriverInterface
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
     * @param  mixed  $value
     *
     * @return void
     */
    public function put(string $storageKey, $value): void
    {
        $this->fileSystem->mkdir($this->fileSystem->dirname($storageKey), true);

        if (! is_scalar($value)) {
            if ($value instanceof StorableArrayable) {
                $value = $value->toStorableArray();
            }

            try {
                $value = serialize($value);
            } catch (Throwable $th) {
                throw new RuntimeException("Error serializing item: {$th->getMessage()}", 1);
            }
        }

        $this->fileSystem->put($storageKey, $value);
    }
}

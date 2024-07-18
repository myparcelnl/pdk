<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Storage;

use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Console\Storage\Contract\ConsoleStorageInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use RuntimeException;
use Throwable;

final class ConsoleFileCacheStorage implements ConsoleStorageInterface
{
    /**
     * @var array
     */
    private static $cacheFiles = [];

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
     * @return void
     */
    public function clear(): void
    {
        foreach (self::$cacheFiles as $file) {
            $this->fileSystem->unlink($file);
        }

        self::$cacheFiles = [];
    }

    /**
     * @param  string $storageKey
     *
     * @return void
     */
    public function delete(string $storageKey): void
    {
        $this->fileSystem->unlink($this->createFilename($storageKey));
    }

    /**
     * @param  string $storageKey
     *
     * @return string
     */
    public function get(string $storageKey): string
    {
        return $this->fileSystem->get($this->createFilename($storageKey));
    }

    /**
     * @param  string $storageKey
     *
     * @return bool
     */
    public function has(string $storageKey): bool
    {
        return $this->fileSystem->fileExists($this->createFilename($storageKey));
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
        $filename = $this->createFilename($storageKey);

        $this->fileSystem->mkdir($this->fileSystem->dirname($filename), true);

        if (! is_scalar($item)) {
            if ($item instanceof StorableArrayable) {
                $item = $item->toStorableArray();
            }

            try {
                $item = serialize($item);
            } catch (Throwable $e) {
                throw new RuntimeException("Error serializing item: {$e->getMessage()}", 1);
            }
        }

        $this->fileSystem->put($filename, $item);

        self::$cacheFiles[] = $filename;
    }

    /**
     * @param  string $storageKey
     *
     * @return string
     */
    private function createFilename(string $storageKey): string
    {
        $formattedKey = preg_replace('/\\\/', '-', strtolower($storageKey));

        return sprintf('%s.cache/console/%s.txt', Pdk::get('rootDir'), $formattedKey);
    }
}

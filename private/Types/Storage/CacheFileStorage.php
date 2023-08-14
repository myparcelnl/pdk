<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Storage;

use MyParcelNL\Pdk\Storage\Contract\StorageInterface;
use RuntimeException;

final class CacheFileStorage implements StorageInterface
{
    public function delete(string $storageKey): void
    {
        unlink($storageKey);
    }

    public function get(string $storageKey)
    {
        $data = file_get_contents($storageKey);

        if ($data === false) {
            return null;
        }
        return $data;
    }

    public function has(string $storageKey): bool
    {
        return file_exists($storageKey);
    }

    public function set(string $storageKey, $item): void
    {
        $this->mkdirp($storageKey);

        if (is_array($item) || is_object($item)) {
            $item = json_encode($item);
        }

        file_put_contents($storageKey, $item);
    }

    /**
     * @param  string $dir
     *
     * @return void
     */
    private function mkdir(string $dir)
    {
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
        }
    }

    /**
     * @param  string $file
     *
     * @return void
     */
    private function mkdirp(string $file)
    {
        $dir = dirname($file);

        if (! is_dir($dir)) {
            $this->mkdirp($dir);
        }

        $this->mkdir($dir);
    }
}

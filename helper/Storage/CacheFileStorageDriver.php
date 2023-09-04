<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Helper\Storage;

use MyParcelNL\Pdk\Storage\Contract\StorageDriverInterface;
use RuntimeException;

final class CacheFileStorageDriver implements StorageDriverInterface
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

    public function put(string $storageKey, $value): void
    {
        $this->mkdirp($storageKey);

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }

        file_put_contents($storageKey, $value);
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

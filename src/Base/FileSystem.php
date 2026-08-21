<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use InvalidArgumentException;
use RuntimeException;

final class FileSystem implements FileSystemInterface
{
    private const DIRECTORY_PERMISSION = 0755;

    /**
     * @param  resource $stream
     *
     * @return void
     */
    public function closeStream($stream): void
    {
        fclose($stream);
    }

    /**
     * @param  string $file
     *
     * @return string
     */
    public function dirname(string $file): string
    {
        return dirname($file);
    }

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function fileExists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * @param  string $path
     *
     * @return string
     */
    public function get(string $path): string
    {
        if (! $this->fileExists($path)) {
            throw new InvalidArgumentException(sprintf('File "%s" does not exist.', $path));
        }

        return file_get_contents($path) ?: '';
    }

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function isDir(string $path): bool
    {
        return is_dir($path);
    }

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function isFile(string $path): bool
    {
        return is_file($path);
    }

    /**
     * @param  string $path
     * @param  bool   $recursive
     *
     * @return void
     */
    public function mkdir(string $path, bool $recursive = false): void
    {
        if (! is_dir($path) && ! mkdir($path, self::DIRECTORY_PERMISSION, $recursive) && ! is_dir($path)) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * @param  string $path
     * @param  string $mode
     *
     * @return null|resource
     */
    public function openStream(string $path, string $mode)
    {
        return fopen($path, $mode) ?: null;
    }

    /**
     * @param  string $path
     * @param  string $contents
     *
     * @return void
     */
    public function put(string $path, string $contents): void
    {
        file_put_contents($path, $contents);
    }

    /**
     * @param  string $path
     *
     * @return string
     */
    public function realpath(string $path): string
    {
        return realpath($path);
    }

    /**
     * @param  string $path
     *
     * @return array
     */
    public function scandir(string $path): array
    {
        return scandir($path);
    }

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function unlink(string $path): bool
    {
        return unlink($path);
    }

    /**
     * @param  resource $stream
     * @param  string   $contents
     *
     * @return void
     */
    public function writeToStream($stream, string $contents): void
    {
        fwrite($stream, $contents);
    }
}

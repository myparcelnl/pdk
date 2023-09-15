<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

interface FileSystemInterface
{
    /**
     * @param  resource $stream
     */
    public function closeStream($stream): void;

    public function dirname(string $file): string;

    public function fileExists(string $path): bool;

    public function get(string $path): string;

    public function isDir(string $path): bool;

    public function isFile(string $path): bool;

    public function mkdir(string $path, bool $recursive = false): void;

    /**
     * @return null|resource
     */
    public function openStream(string $path, string $mode);

    public function put(string $path, string $contents): void;

    public function realpath(string $path): string;

    public function scandir(string $path): array;

    public function unlink(string $path): bool;

    /**
     * @param  resource $stream
     */
    public function writeToStream($stream, string $contents): void;
}

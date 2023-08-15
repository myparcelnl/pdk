<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

interface FileSystemInterface
{
    /**
     * @param  resource $stream
     *
     * @return void
     */
    public function closeStream($stream): void;

    /**
     * @param  string $file
     *
     * @return string
     */
    public function dirname(string $file): string;

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function fileExists(string $path): bool;

    /**
     * @param  string $path
     *
     * @return string
     */
    public function get(string $path): string;

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function isDir(string $path): bool;

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function isFile(string $path): bool;

    /**
     * @param  string $path
     * @param  bool   $recursive
     *
     * @return void
     */
    public function mkdir(string $path, bool $recursive = false): void;

    /**
     * @param  string $path
     * @param  string $mode
     *
     * @return null|resource
     */
    public function openStream(string $path, string $mode);

    /**
     * @param  string $path
     * @param  string $contents
     *
     * @return void
     */
    public function put(string $path, string $contents): void;

    /**
     * @param  string $path
     *
     * @return string
     */
    public function realpath(string $path): string;

    /**
     * @param  string $path
     *
     * @return array
     */
    public function scandir(string $path): array;

    /**
     * @param  string $path
     *
     * @return bool
     */
    public function unlink(string $path): bool;

    /**
     * @param  resource $stream
     * @param  string   $contents
     *
     * @return void
     */
    public function writeToStream($stream, string $contents): void;
}

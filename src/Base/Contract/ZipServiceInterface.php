<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface ZipServiceInterface
{
    /**
     * Add a file to the zip archive. Optionally specify a target filename.
     */
    public function addFile(string $filename, ?string $targetFilename = null): void;

    /**
     * Add a file to the zip archive from a string.
     */
    public function addFromString(string $string, string $targetFilename): void;

    /**
     * Finish writing the zip archive and close the file.
     */
    public function close(): void;

    /**
     * Create a new zip archive.
     */
    public function create(string $filename);
}

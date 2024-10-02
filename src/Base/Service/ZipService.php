<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\ZipServiceInterface;
use RuntimeException;
use ZipArchive;

class ZipService implements ZipServiceInterface
{
    /**
     * @var null|\ZipArchive
     */
    private $currentFile;

    /**
     * @param  string      $filename
     * @param  null|string $targetFilename
     *
     * @return void
     * @throws \RuntimeException
     */
    public function addFile(string $filename, ?string $targetFilename = null): void
    {
        $this->validateHasFile();
        $success = $this->currentFile->addFile($filename, $targetFilename ?? basename($filename));

        if (! $success) {
            throw new RuntimeException('Failed to add file to zip');
        }
    }

    /**
     * @param  string $string
     * @param  string $targetFilename
     *
     * @return void
     */
    public function addFromString(string $string, string $targetFilename): void
    {
        $this->validateHasFile();
        $this->currentFile->addFromString($targetFilename, $string);
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->validateHasFile();
        $this->currentFile->close();
        $this->currentFile = null;
    }

    /**
     * @param  string $filename
     *
     * @return void
     */
    public function create(string $filename): void
    {
        $zip = new ZipArchive();

        $zip->open($filename, ZipArchive::CREATE);

        $this->currentFile = $zip;
    }

    /**
     * @return void
     */
    private function validateHasFile(): void
    {
        if (null !== $this->currentFile) {
            return;
        }

        throw new RuntimeException('No zip file is open');
    }
}

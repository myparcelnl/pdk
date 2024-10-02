<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\ZipServiceInterface;
use MyParcelNL\Pdk\Base\Exception\ZipException;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use ZipArchive;

class ZipService implements ZipServiceInterface
{
    /**
     * @var null|\ZipArchive
     */
    private $currentFile;

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
     * @param  string      $filename
     * @param  null|string $targetFilename
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\ZipException
     */
    public function addFile(string $filename, ?string $targetFilename = null): void
    {
        $this->validateHasFile();

        $success = $this->currentFile->addFile($filename, $targetFilename ?? basename($filename));

        if (! $success) {
            throw new ZipException('Failed to add file to zip');
        }
    }

    /**
     * @param  string $string
     * @param  string $targetFilename
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\ZipException
     */
    public function addFromString(string $string, string $targetFilename): void
    {
        $this->validateHasFile();
        $this->currentFile->addFromString($targetFilename, $string);
    }

    /**
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\ZipException
     */
    public function close(): void
    {
        $this->validateHasFile();
        $success = $this->currentFile->close();

        if ($success) {
            $this->currentFile = null;
        } else {
            throw new ZipException('Failed to close zip file');
        }
    }

    /**
     * @param  string $filename
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\ZipException
     */
    public function create(string $filename): void
    {
        $zip     = new ZipArchive();
        $dirname = $this->fileSystem->dirname($filename);

        $this->fileSystem->mkdir($dirname, true);

        $success = $zip->open($filename, ZipArchive::CREATE);

        if ($success) {
            $this->currentFile = $zip;
        } else {
            throw new ZipException('Failed to create zip file');
        }
    }

    /**
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\ZipException
     */
    private function validateHasFile(): void
    {
        if (null !== $this->currentFile) {
            return;
        }

        throw new ZipException('No zip file is open');
    }
}

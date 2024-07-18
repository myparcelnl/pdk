<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Service;

use MyParcelNL\Pdk\Base\Contract\ZipServiceInterface;
use ZipArchive;

class ZipService implements ZipServiceInterface
{
    /**
     * @var \ZipArchive
     */
    private $currentFile;

    /**
     * @param  string      $filename
     * @param  null|string $targetFilename
     *
     * @return void
     */
    public function addFile(string $filename, ?string $targetFilename): void
    {
        $this->currentFile->addFile($filename, $targetFilename ?? $filename);
    }

    /**
     * @param  string $string
     * @param  string $targetFilename
     *
     * @return void
     */
    public function addFromString(string $string, string $targetFilename): void
    {
        $this->currentFile->addFromString($targetFilename, $string);
    }

    /**
     * @return void
     */
    public function close(): void
    {
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
}

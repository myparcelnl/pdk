<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Uses;

use MyParcelNL\Pdk\Base\FileSystem;
use MyParcelNL\Pdk\Base\FileSystemInterface;
use function DI\get;
use function MyParcelNL\Pdk\Tests\deleteTemporaryFiles;
use function MyParcelNL\Pdk\Tests\mockPdkProperty;

/**
 * Sets the file system interface in the pdk to the real one and deletes temporary files after each test.
 */
final class UsesRealFileSystem implements BaseMock
{
    public function afterEach(): void
    {
        deleteTemporaryFiles();
    }

    public function beforeAll(): void
    {
        mockPdkProperty(FileSystemInterface::class, get(FileSystem::class));
    }
}

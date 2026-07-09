<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Hook;

use PHPUnit\Runner\BeforeFirstTestHook;

final class ClearContainerCacheHook implements BeforeFirstTestHook
{
    private const CACHE_DIR = __DIR__ . '/../../.cache';

    public function executeBeforeFirstTest(): void
    {
        putenv('PDK_DISABLE_CACHE=1');

        $this->deleteDirectoryContents(self::CACHE_DIR);
    }

    private function deleteDirectoryContents(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (array_diff(scandir($directory) ?: [], ['.', '..']) as $entry) {
            $path = "$directory/$entry";

            if (is_dir($path) && ! is_link($path)) {
                $this->deleteDirectoryContents($path);
                rmdir($path);
                continue;
            }

            unlink($path);
        }
    }
}

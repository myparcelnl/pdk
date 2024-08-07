<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Hook;

use PHPUnit\Runner\BeforeFirstTestHook;

final class ClearContainerCacheHook implements BeforeFirstTestHook
{
    private const CACHE_DIR            = __DIR__ . '/../../.cache';
    private const CONTAINER_CACHE_FILE = self::CACHE_DIR . '/CompiledContainer.php';

    public function executeBeforeFirstTest(): void
    {
        putenv('PDK_DISABLE_CACHE=1');

        if (! is_dir(self::CACHE_DIR) || ! is_file(self::CONTAINER_CACHE_FILE)) {
            return;
        }

        unlink(self::CONTAINER_CACHE_FILE);
    }
}

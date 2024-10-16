<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Hook;

use PHPUnit\Runner\AfterLastTestHook;

final class DeleteTemporaryFilesHook implements AfterLastTestHook
{
    private const TMP_DIR = __DIR__ . '/../../.tmp';

    /**
     * @return void
     */
    public function executeAfterLastTest(): void
    {
        $this->deleteDirectory(self::TMP_DIR);
    }

    /**
     * @param  string $dir
     * @param  bool   $deleteDir
     *
     * @return void
     */
    private function deleteDirectory(string $dir, bool $deleteDir = false): void
    {
        $paths = scandir($dir);

        foreach ($paths as $path) {
            if ('.' === $path || '..' === $path) {
                continue;
            }

            if (is_dir("$dir/$path")) {
                $this->deleteDirectory("$dir/$path", true);
            } else {
                unlink("$dir/$path");
            }
        }

        if ($deleteDir) {
            rmdir($dir);
        }
    }
}

<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Hook;

use PHPUnit\Runner\AfterLastTestHook;

final class DeleteTemporaryFilesHook implements AfterLastTestHook
{
    /**
     * @return void
     */
    public function executeAfterLastTest(): void
    {
        $files = scandir('.tmp/');

        foreach ($files as $file) {
            if ('.' === $file || '..' === $file) {
                continue;
            }

            if (is_dir(".tmp//$file")) {
                rmdir(".tmp//$file");
            } else {
                unlink(".tmp//$file");
            }
        }
    }
}

<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Hook;

use PHPUnit\Runner\AfterLastTestHook;
use function MyParcelNL\Pdk\Tests\deleteTemporaryFiles;

final class DeleteTemporaryFilesHook implements AfterLastTestHook
{
    /**
     * @return void
     */
    public function executeAfterLastTest(): void
    {
        deleteTemporaryFiles();
    }
}

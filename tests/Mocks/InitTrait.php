<?php
/** @noinspection PhpUndefinedFieldInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

trait InitTrait
{
    public function initializeInitTrait(): void
    {
        $this->myProperty = 1;
    }
}

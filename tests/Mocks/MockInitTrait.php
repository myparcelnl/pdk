<?php
/** @noinspection PhpUndefinedFieldInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Mocks;

trait MockInitTrait
{
    public function initializeMockInitTrait(): void
    {
        $this->myProperty = 1;
    }
}

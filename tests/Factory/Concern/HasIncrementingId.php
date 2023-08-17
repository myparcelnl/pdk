<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory\Concern;

trait HasIncrementingId
{
    protected static $id = 0;

    /**
     * @return int
     */
    protected function getNextId(): int
    {
        return ++static::$id;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory;

use Symfony\Contracts\Service\ResetInterface;

final class SharedFactoryState implements ResetInterface
{
    public static $ids = [];

    /**
     * @param  string $key
     *
     * @return int
     */
    public function getNextId(string $key): int
    {
        if (! isset(self::$ids[$key])) {
            self::$ids[$key] = 0;
        }

        return ++self::$ids[$key];
    }

    public function reset(): void
    {
        self::$ids = [];
    }
}

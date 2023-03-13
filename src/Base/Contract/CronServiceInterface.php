<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface CronServiceInterface
{
    /**
     * Dispatch an action now.
     */
    public function dispatch(callable $callback, ...$args): void;

    /**
     * Schedule an action for later.
     */
    public function schedule(callable $callback, int $timestamp, ...$args): void;
}

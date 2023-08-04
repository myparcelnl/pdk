<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface CronServiceInterface
{
    /**
     * Dispatch an action now.
     *
     * @param  callable|string|callable-string $callback
     */
    public function dispatch($callback, ...$args): void;

    /**
     * Schedule an action for later.
     *
     * @param  callable|string|callable-string $callback
     */
    public function schedule($callback, int $timestamp, ...$args): void;
}

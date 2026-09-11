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
     * Throws when the platform refused to schedule, so a caller is never told work is queued when
     * none is. An identical action that is already waiting counts as scheduled, not as a failure.
     *
     * @param  callable|string|callable-string $callback
     *
     * @throws \Throwable When the action could not be scheduled.
     */
    public function schedule($callback, int $timestamp, ...$args): void;
}

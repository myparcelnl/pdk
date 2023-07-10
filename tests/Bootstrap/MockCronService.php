<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\Contract\CronServiceInterface;
use MyParcelNL\Pdk\Base\Support\Collection;

final class MockCronService implements CronServiceInterface
{
    /**
     * Add an id to be able to identify the task.
     *
     * @var int
     */
    private $incrementingId = 1;

    /**
     * @var Collection|{callback: callable, args: array}[]
     */
    private $scheduledTasks;

    public function __construct()
    {
        $this->clearScheduledTasks();
    }

    /**
     * @return void
     */
    public function clearScheduledTasks(): void
    {
        $this->scheduledTasks = new Collection();
    }

    /**
     * @param  callable $callback
     * @param           ...$args
     *
     * @return void
     */
    public function dispatch(callable $callback, ...$args): void
    {
        $this->schedule($callback, time(), ...$args);
    }

    /**
     * @return void
     */
    public function executeAllTasks(): void
    {
        while ($this->scheduledTasks->isNotEmpty()) {
            $this->executeScheduledTask();
        }
    }

    /**
     * Execute the first scheduled task.
     *
     * @return void
     */
    public function executeScheduledTask(): void
    {
        $task = $this->scheduledTasks
            ->sortBy('timestamp')
            ->first();

        if (! $task) {
            return;
        }

        call_user_func($task['callback'], ...$task['args']);

        $this->scheduledTasks->forget($task['id']);
    }

    /**
     * @return Collection
     */
    public function getScheduledTasks(): Collection
    {
        return $this->scheduledTasks;
    }

    /**
     * @param  callable $callback
     * @param  int      $timestamp
     * @param           ...$args
     *
     * @return void
     */
    public function schedule(callable $callback, int $timestamp, ...$args): void
    {
        $this->scheduledTasks->put($this->incrementingId, [
            'id'        => $this->incrementingId,
            'callback'  => $callback,
            'timestamp' => $timestamp,
            'args'      => $args,
        ]);

        $this->incrementingId++;
    }
}

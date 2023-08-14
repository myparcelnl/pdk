<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Concern;

trait HasLogging
{
    /**
     * @param  float $time
     *
     * @return float
     */
    protected function getPassedTimeSince(float $time): float
    {
        return microtime(true) - $time;
    }

    /**
     * @return float
     */
    protected function getTime(): float
    {
        return microtime(true);
    }

    /**
     * @param ...$args
     *
     * @return void
     */
    protected function log(...$args): void
    {
        echo implode(' ', $args) . PHP_EOL;
    }

    /**
     * @param  float $time
     *
     * @return string
     */
    protected function printTimeSince(float $time): string
    {
        return sprintf('%s ms', round($this->getPassedTimeSince($time) * 1000, 2));
    }
}

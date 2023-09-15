<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Types\Shared\Concern;

trait ReportsTiming
{
    protected function getPassedTimeSince(float $time): float
    {
        return microtime(true) - $time;
    }

    protected function getTime(): float
    {
        return microtime(true);
    }

    protected function printTimeSince(float $time): string
    {
        return sprintf('%s ms', round($this->getPassedTimeSince($time) * 1000, 2));
    }
}

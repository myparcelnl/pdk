<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Logger\AbstractLogger;

class MockLogger extends AbstractLogger
{
    private array $logs = [];

    public function clear(): void
    {
        $this->logs = [];
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * @param        $level
     * @param        $message
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logs[] = [
            'level'   => $level,
            'message' => $message,
            'context' => $context,
        ];
    }
}

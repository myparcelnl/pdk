<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Logger\AbstractLogger;

class MockLogger extends AbstractLogger
{
    /**
     * @var array
     */
    private $logs = [];

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->logs = [];
    }

    /**
     * @return void
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * @param        $level
     * @param        $message
     * @param  array $context
     *
     * @return void
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

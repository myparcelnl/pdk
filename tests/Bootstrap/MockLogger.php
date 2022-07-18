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
    public function getLogs(): array
    {
        return $this->logs;
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logs[] = [
            'level'   => $level,
            'message' => $message,
            'context' => $context,
        ];
    }
}

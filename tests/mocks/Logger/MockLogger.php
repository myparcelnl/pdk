<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Logger;

use MyParcelNL\Pdk\Contract\MockServiceInterface;

final class MockLogger extends AbstractLogger implements MockServiceInterface
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

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->logs = [];
    }
}

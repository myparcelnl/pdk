<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Logger\Contract;

use Psr\Log\LoggerInterface;

interface PdkLoggerInterface extends LoggerInterface
{
    /**
     * @param  string      $subject     The thing that has been deprecated
     * @param  null|string $replacement The thing that will be its replacement
     * @param  array       $context
     *
     * @return void
     */
    public function deprecated(string $subject, ?string $replacement = null, array $context = []): void;

    /**
     * Get all logs as an associative array with log levels as keys and log file paths as values.
     *
     * @return array{notice?: string, warning?: string, error?: string, critical?: string, alert?: string, emergency?: string}
     */
    public function getLogFiles(): array;
}

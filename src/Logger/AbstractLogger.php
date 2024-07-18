<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Logger;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;
use Psr\Log\LogLevel;

abstract class AbstractLogger implements PdkLoggerInterface
{
    /**
     * @param        $level
     * @param        $message
     * @param  array $context
     *
     * @return void
     */
    abstract public function log($level, $message, array $context = []): void;

    /**
     * @param  string $message
     * @param  array  $context
     *
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->createLog(LogLevel::ALERT, $message, $context);
    }

    /**
     * @param  string $message
     * @param  array  $context
     *
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->createLog(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * @param  string $message
     * @param  array  $context
     *
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->createLog(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @param  string      $subject
     * @param  null|string $replacement
     * @param  array       $context
     *
     * @return void
     */
    public function deprecated(string $subject, ?string $replacement = null, array $context = []): void
    {
        $message = "[DEPRECATED] $subject is deprecated.";

        if ($replacement) {
            $message .= " Use $replacement instead.";
        }

        $version = Pdk::get('pdkNextMajorVersion');
        $message .= " Will be removed in $version.";

        $this->notice($message, $context);
    }

    /**
     * @param  string $message
     * @param  array  $context
     *
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->createLog(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @param  string $message
     * @param  array  $context
     *
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->createLog(LogLevel::ERROR, $message, $context);
    }

    /**
     * @TODO: remove this default in v3.0.0, for now it's here to prevent breaking changes
     * @return array
     */
    public function getLogs(): array
    {
        return [];
    }

    /**
     * @param  string $message
     * @param  array  $context
     *
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->createLog(LogLevel::INFO, $message, $context);
    }

    /**
     * @param  string $message
     * @param  array  $context
     *
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->createLog(LogLevel::NOTICE, $message, $context);
    }

    /**
     * @param  string $message
     * @param  array  $context
     *
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->createLog(LogLevel::WARNING, $message, $context);
    }

    /**
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     *
     * @return void
     */
    protected function createLog(string $level, string $message, array $context): void
    {
        $message = "[PDK]: $message";

        $this->log($level, $message, $context);
    }
}


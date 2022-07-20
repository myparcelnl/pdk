<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

abstract class AbstractLogger implements LoggerInterface
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


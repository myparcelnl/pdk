<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Logger;

use MyParcelNL\Pdk\Base\Contract\LoggerInterface;
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
     * @param  string      $class
     * @param  null|string $replacement
     *
     * @return void
     */
    public function reportDeprecatedClass(string $class, ?string $replacement = null): void
    {
        $this->logDeprecation("Class $class", $replacement);
    }

    /**
     * @param  string      $interface
     * @param  null|string $replacement
     *
     * @return void
     */
    public function reportDeprecatedInterface(string $interface, ?string $replacement = null): void
    {
        $this->logDeprecation("Interface $interface", $replacement);
    }

    /**
     * @param  string      $method
     * @param  string|null $replacement
     *
     * @return void
     */
    public function reportDeprecatedMethod(string $method, ?string $replacement = null): void
    {
        $this->logDeprecation("Method $method()", $replacement);
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

    /**
     * @param  string      $thing
     * @param  null|string $replacement
     *
     * @return void
     */
    protected function logDeprecation(string $thing, ?string $replacement = null): void
    {
        $message = sprintf('%s is deprecated and will be removed in the next major release.', $thing);

        if ($replacement) {
            $message .= sprintf(' Use %s instead.', $replacement);
        }

        $this->warning($message);
    }
}


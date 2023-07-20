<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Contract;

interface LoggerInterface extends \Psr\Log\LoggerInterface
{
    /**
     * @param  string      $class
     * @param  null|string $replacement
     *
     * @return void
     */
    public function reportDeprecatedClass(string $class, ?string $replacement = null): void;

    /**
     * @param  string      $interface
     * @param  null|string $replacement
     *
     * @return void
     */
    public function reportDeprecatedInterface(string $interface, ?string $replacement = null): void;

    /**
     * Log a message when a deprecated method is called.
     *
     * @param  string      $method
     * @param  string|null $replacement
     *
     * @return void
     */
    public function reportDeprecatedMethod(string $method, ?string $replacement = null): void;
}

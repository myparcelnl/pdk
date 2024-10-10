<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Facade;

use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface;

/**
 * @method static void log($level, $message, array $context = [])
 * @method static void alert($message, array $context = [])
 * @method static void critical($message, array $context = [])
 * @method static void debug($message, array $context = [])
 * @method static void emergency($message, array $context = [])
 * @method static void error($message, array $context = [])
 * @method static void info($message, array $context = [])
 * @method static void notice($message, array $context = [])
 * @method static void warning($message, array $context = [])
 * @method static void deprecated(string $subject, string $replacement = null, array $context = [])
 * @method static array getLogFiles()
 * @see \MyParcelNL\Pdk\Logger\Contract\PdkLoggerInterface
 */
final class Logger extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return PdkLoggerInterface::class;
    }
}

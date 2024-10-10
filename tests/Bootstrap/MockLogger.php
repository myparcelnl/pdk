<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Base\FileSystemInterface;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Logger\AbstractLogger;
use Psr\Log\LogLevel;
use function array_filter;
use function array_reduce;
use function json_encode;

class MockLogger extends AbstractLogger
{
    private const ALL_LOG_LEVELS = [
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::DEBUG,
        LogLevel::EMERGENCY,
        LogLevel::ERROR,
        LogLevel::INFO,
        LogLevel::NOTICE,
        LogLevel::WARNING,
    ];

    /**
     * @var \MyParcelNL\Pdk\Base\FileSystemInterface
     */
    private $fileSystem;

    /**
     * @var array
     */
    private $logs = [];

    /**
     * @var array<string, resource>
     */
    private $streams = [];

    /**
     * @param  \MyParcelNL\Pdk\Base\FileSystemInterface $fileSystem
     */
    public function __construct(FileSystemInterface $fileSystem)
    {
        $this->fileSystem = $fileSystem;

        $appInfo = Pdk::getAppInfo();
        $this->fileSystem->mkdir($appInfo->createPath('logs'), true);

        foreach (self::ALL_LOG_LEVELS as $level) {
            $filename = $appInfo->createPath("logs/test_$level.log");

            $this->streams[$level] = $this->fileSystem->openStream($filename, 'w');
        }
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->logs = [];
    }

    /**
     * @return string[]
     */
    public function getLogFiles(): array
    {
        $appInfo = Pdk::getAppInfo();

        return array_reduce(self::ALL_LOG_LEVELS, static function (array $acc, string $level) use ($appInfo) {
            $acc[$level] = $appInfo->createPath("logs/test_$level.log");

            return $acc;
        }, []);
    }

    /**
     * @return array
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

        $formattedString = implode(' ', array_filter([
            "!$level!",
            $message,
            empty($context) ? null : json_encode($context),
        ]));

        $this->fileSystem->writeToStream($this->streams[$level], $formattedString . PHP_EOL);
    }
}

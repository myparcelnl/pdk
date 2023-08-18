<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Factory;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Pdk as PdkInstance;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;

final class MockPdkFactory extends PdkFactory
{
    private const CACHE_FILE = MockPdkFactory::CACHE_DIR . '/CompiledContainer.php';
    private const CACHE_DIR  = __DIR__ . '/../../.cache';

    public static function clear(): void
    {
        Facade::setPdkInstance(null);
    }

    /**
     * @param  array|string ...$config
     *
     * @return \MyParcelNL\Pdk\Base\Concern\PdkInterface
     * @throws \Exception
     */
    public static function create(...$config): PdkInterface
    {
        self::disableContainerCache();
        self::setMode(PdkInstance::MODE_PRODUCTION);

        return parent::create(MockPdkConfig::create(...$config));
    }

    /**
     * @return void
     */
    private static function disableContainerCache(): void
    {
        putenv('PDK_DISABLE_CACHE=1');

        if (! is_dir(self::CACHE_DIR) || ! is_file(self::CACHE_FILE)) {
            return;
        }

        unlink(self::CACHE_FILE);
    }
}

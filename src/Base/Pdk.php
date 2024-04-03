<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use DI\Container;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Exception\PdkConfigException;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use Throwable;

class Pdk implements PdkInterface
{
    public const PACKAGE_NAME     = 'myparcelnl/pdk';
    public const MODE_DEVELOPMENT = 'development';
    public const MODE_PRODUCTION  = 'production';
    /**
     * The directory where the container cache file will be stored.
     */
    public const CACHE_DIR = __DIR__ . '/../../.cache';
    /**
     * The container cache class name.
     */
    public const CACHE_CLASS_NAME = 'CompiledContainer';
    /**
     * The full path to the container cache file.
     */
    private const CACHE_FILE_PATH = self::CACHE_DIR . '/' . self::CACHE_CLASS_NAME . '.php';

    /**
     * @var \DI\Container
     */
    protected $container;

    /**
     * @throws \Exception
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Delete the container cache file if it exists.
     *
     * @return void
     */
    public function clearCache(): void
    {
        if (! file_exists(self::CACHE_FILE_PATH)) {
            return;
        }

        unlink(self::CACHE_FILE_PATH);
    }

    /**
     * @param  string $key
     *
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function get(string $key)
    {
        return $this->container->get($key);
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Model\AppInfo
     * @throws \MyParcelNL\Pdk\Base\Exception\PdkConfigException
     */
    public function getAppInfo(): AppInfo
    {
        try {
            /** @var \MyParcelNL\Pdk\Base\Model\AppInfo $appInfo */
            $appInfo = $this->get('appInfo');
        } catch (Throwable $e) {
            throw new PdkConfigException('The appInfo property is missing.');
        }

        if (! $appInfo instanceof AppInfo) {
            throw new PdkConfigException('The appInfo property is not an instance of AppInfo.');
        }

        return $appInfo;
    }

    /**
     * @return \DI\Container
     */
    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @return string
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function getMode(): string
    {
        return $this->get('mode');
    }

    /**
     * @param  string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->container->has($key);
    }

    /**
     * @return bool
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @noinspection PhpUnused
     */
    public function isDevelopment(): bool
    {
        return self::MODE_DEVELOPMENT === $this->getMode();
    }

    /**
     * @return bool
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @noinspection PhpUnused
     */
    public function isProduction(): bool
    {
        return self::MODE_PRODUCTION === $this->getMode();
    }
}

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
     * Delete all container cache files (compiled containers and proxies of any version),
     * invalidating OPcache entries along the way so a deleted file cannot keep being served.
     *
     * @return void
     */
    public function clearCache(): void
    {
        $this->deleteDirectoryContents(self::getCacheDir());
    }

    /**
     * @return string
     */
    public static function getCacheDir(): string
    {
        $cacheDir = self::CACHE_DIR;
        $realPath = realpath($cacheDir);

        if (false !== $realPath) {
            return $realPath;
        }

        $parent = realpath(dirname($cacheDir));

        return false !== $parent
            ? sprintf('%s/%s', $parent, basename($cacheDir))
            : $cacheDir;
    }

    /**
     * @param  string $directory
     *
     * @return void
     */
    private function deleteDirectoryContents(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $entries = @scandir($directory);

        if (false === $entries) {
            return;
        }

        foreach (array_diff($entries, ['.', '..']) as $entry) {
            $path = "$directory/$entry";

            if (is_dir($path) && ! is_link($path)) {
                $this->deleteDirectoryContents($path);
                @rmdir($path);
                continue;
            }

            if (function_exists('opcache_invalidate') && 'php' === pathinfo($path, PATHINFO_EXTENSION)) {
                @opcache_invalidate($path, true);
            }

            @unlink($path);
        }
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

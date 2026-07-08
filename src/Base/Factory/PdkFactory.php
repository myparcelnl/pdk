<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Factory;

use DI\Container;
use DI\ContainerBuilder;
use DI\Definition\Source\SourceCache;
use InvalidArgumentException;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Contract\PdkFactoryInterface;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Pdk;
use function DI\value;

class PdkFactory implements PdkFactoryInterface
{
    private const MODES       = [Pdk::MODE_PRODUCTION, Pdk::MODE_DEVELOPMENT];
    private const CONFIG_PATH = __DIR__ . '/../../../config';

    /**
     * @var null|string
     */
    protected static $cacheVersion;

    /**
     * @var null|string
     */
    protected static $mode;

    public function __construct() { }

    /**
     * @param  array[]|string[] $config
     *
     * @return \MyParcelNL\Pdk\Base\Concern\PdkInterface
     * @throws \Exception
     */
    public static function create(...$config): PdkInterface
    {
        $instance  = new static();
        $container = $instance->setupContainer(...$config);

        $pdk = new Pdk($container);

        Facade::setPdkInstance($pdk);

        return $pdk;
    }

    /**
     * Set the app version to key the container caches by. Without this, caches persist across
     * up- and downgrades (most notably the APCu definition cache, which survives replacing the
     * plugin directory) and poison the compiled container with definitions from another version.
     *
     * @param  null|string $version
     *
     * @return void
     */
    public static function setCacheVersion(?string $version): void
    {
        $sanitized = null === $version ? null : preg_replace('/[^\w.-]+/', '_', $version);

        // Reject values that would escape or collapse the cache directory, e.g. "..", "." or "".
        if (null === $sanitized || '' === trim($sanitized, '.') || false !== strpos($sanitized, '..')) {
            self::$cacheVersion = null;

            return;
        }

        self::$cacheVersion = $sanitized;
    }

    /**
     * @param  string $mode
     *
     * @return void
     */
    public static function setMode(string $mode): void
    {
        if (! in_array($mode, self::MODES, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid mode. Valid modes are: %s', implode(', ', self::MODES))
            );
        }

        self::$mode = $mode;
    }

    /**
     * @return string
     */
    protected function getMode(): string
    {
        return self::$mode ?? getenv('PDK_MODE') ?: Pdk::MODE_PRODUCTION;
    }

    /**
     * Caches the container definitions and proxies. This is only done in production mode.
     *
     * @param  \DI\ContainerBuilder $builder
     *
     * @return void
     */
    protected function setupCache(ContainerBuilder $builder): void
    {
        $cacheVersion = $this->getCacheVersion();
        $cacheDir     = Pdk::getCacheDir() . '/' . $cacheVersion;

        if (SourceCache::isSupported()) {
            $builder->enableDefinitionCache("pdk-definition-cache-$cacheVersion");
        }

        $builder->enableCompilation($cacheDir, $this->getCacheClassName());
        $builder->writeProxiesToFile(true, $cacheDir);
    }

    /**
     * @return string
     */
    protected function getCacheVersion(): string
    {
        return self::$cacheVersion ?? 'default';
    }

    /**
     * @return string
     */
    protected function getCacheClassName(): string
    {
        return sprintf(
            '%s_%s',
            Pdk::CACHE_CLASS_NAME,
            preg_replace('/\W+/', '_', $this->getCacheVersion())
        );
    }

    /**
     * @param  array[]|string[] $configs
     *
     * @return \DI\Container
     * @throws \Exception
     */
    protected function setupContainer(...$configs): Container
    {
        $mode       = $this->getMode();
        $configPath = realpath(self::CONFIG_PATH) ?: self::CONFIG_PATH;
        $builder    = new ContainerBuilder();

        $builder->useAutowiring(true);
        $builder->addDefinitions(
            sprintf('%s/pdk-default.php', $configPath),
            sprintf('%s/pdk-template.php', $configPath),
            sprintf('%s/pdk-business-logic.php', $configPath),
            sprintf('%s/pdk-dependencies.php', $configPath),
            sprintf('%s/pdk-fields.php', $configPath),
            sprintf('%s/pdk-services.php', $configPath),
            sprintf('%s/pdk-settings.php', $configPath),
            ['mode' => value($mode)],
            ...$configs
        );

        if (Pdk::MODE_PRODUCTION === $mode && ! getenv('PDK_DISABLE_CACHE')) {
            $this->setupCache($builder);
        }

        return $builder->build();
    }
}

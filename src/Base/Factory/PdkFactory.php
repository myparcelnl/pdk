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
        self::$cacheVersion = null === $version ? null : preg_replace('/[^\w.-]+/', '_', $version);
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
        $cacheDir     = Pdk::CACHE_DIR . '/' . $cacheVersion;

        if (SourceCache::isSupported()) {
            $builder->enableDefinitionCache("pdk-definition-cache-$cacheVersion");
        }

        $builder->enableCompilation($cacheDir, Pdk::CACHE_CLASS_NAME);
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
     * @param  array[]|string[] $configs
     *
     * @return \DI\Container
     * @throws \Exception
     */
    protected function setupContainer(...$configs): Container
    {
        $mode    = $this->getMode();
        $builder = new ContainerBuilder();

        $builder->useAutowiring(true);
        $builder->addDefinitions(
            sprintf('%s/pdk-default.php', self::CONFIG_PATH),
            sprintf('%s/pdk-template.php', self::CONFIG_PATH),
            sprintf('%s/pdk-business-logic.php', self::CONFIG_PATH),
            sprintf('%s/pdk-dependencies.php', self::CONFIG_PATH),
            sprintf('%s/pdk-fields.php', self::CONFIG_PATH),
            sprintf('%s/pdk-services.php', self::CONFIG_PATH),
            sprintf('%s/pdk-settings.php', self::CONFIG_PATH),
            ['mode' => value($mode)],
            ...$configs
        );

        if (Pdk::MODE_PRODUCTION === $mode && ! getenv('PDK_DISABLE_CACHE')) {
            $this->setupCache($builder);
        }

        return $builder->build();
    }
}

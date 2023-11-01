<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Factory;

use DI\Container;
use DI\ContainerBuilder;
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
        if (function_exists('apcu_fetch')) {
            $builder->enableDefinitionCache('pdk-definition-cache');
        }

        $builder->enableCompilation(Pdk::CACHE_DIR, Pdk::CACHE_CLASS_NAME);
        $builder->writeProxiesToFile(true, Pdk::CACHE_DIR);
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

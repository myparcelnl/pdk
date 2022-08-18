<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Factory;

use DI\Container;
use DI\ContainerBuilder;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Pdk;

class PdkFactory
{
    private const DEFAULT_CONFIG_PATH = __DIR__ . '/../../../config/default.php';

    protected static $index = 0;

    /**
     * @var \DI\Container
     */
    public $container;

    /**
     * @param  array[]|string[] $config
     *
     * @return \MyParcelNL\Pdk\Base\Pdk
     * @throws \Exception
     */
    public static function create(...$config): Pdk
    {
        $container = self::setupContainer(...$config);
        $pdk       = new Pdk($container);

        $container->set(Pdk::class, $pdk);

        Facade::setPdkInstance($pdk);

        return $pdk;
    }

    /**
     * @param  array[]|string[] $configs
     *
     * @return \DI\Container
     * @throws \Exception
     */
    private static function setupContainer(...$configs): Container
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(true);
        $builder->addDefinitions(self::DEFAULT_CONFIG_PATH, ...$configs);

        return $builder->build();
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Factory;

use DI\Container;
use DI\ContainerBuilder;
use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Pdk;

class PdkFactory
{
    private const CONFIG_PATH = __DIR__ . '/../../../config';

    /**
     * @param  array[]|string[] $config
     *
     * @return \MyParcelNL\Pdk\Base\Concern\PdkInterface
     * @throws \Exception
     */
    public static function create(...$config): PdkInterface
    {
        $container = self::setupContainer(...$config);
        $pdk       = new Pdk($container);

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
        $builder->addDefinitions(
            sprintf('%s/pdk-default.php', self::CONFIG_PATH),
            sprintf('%s/pdk-template.php', self::CONFIG_PATH),
            sprintf('%s/pdk-business-logic.php', self::CONFIG_PATH),
            sprintf('%s/pdk-dependencies.php', self::CONFIG_PATH),
            sprintf('%s/pdk-fields.php', self::CONFIG_PATH),
            sprintf('%s/pdk-services.php', self::CONFIG_PATH),
            sprintf('%s/pdk-settings.php', self::CONFIG_PATH),
            ...$configs
        );

        return $builder->build();
    }
}

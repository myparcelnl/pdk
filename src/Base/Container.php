<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use DI\ContainerBuilder;

class Container
{
    /**
     * @var \DI\Container
     */
    protected static $instance;

    /**
     * Private to disable instantiation.
     */
    private function __construct() { }

    /**
     * @return \DI\Container
     * @throws \Exception
     */
    public static function getInstance(): \DI\Container
    {
        if (! self::$instance) {
            $builder        = new ContainerBuilder();
            self::$instance = $builder->build();
        }

        return self::$instance;
    }
}

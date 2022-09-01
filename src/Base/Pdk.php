<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use DI\Container;

class Pdk
{
    public const PACKAGE_NAME = 'myparcelnl/pdk';

    /**
     * @var \DI\Container
     */
    private $container;

    /**
     * @throws \Exception
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
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
     * @param  string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->container->has($key);
    }
}

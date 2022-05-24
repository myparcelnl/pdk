<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

class Pdk
{
    /**
     * @var \DI\Container
     */
    private $container;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->container = Container::getInstance();
    }

    /**
     * @param  string $responseClass
     *
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function get(string $responseClass)
    {
        return $this->container->get($responseClass);
    }

    /**
     * @param  string $responseClass
     *
     * @return bool
     */
    public function has(string $responseClass): bool
    {
        return $this->container->has($responseClass);
    }
}

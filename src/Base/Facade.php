<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Exception\InvalidFacadeException;

abstract class Facade
{
    /**
     * @var \MyParcelNL\Pdk\Base\Concern\PdkInterface|null
     */
    protected static $pdk;

    /**
     * @param  string $method
     * @param  mixed  $args
     *
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidFacadeException
     */
    public static function __callStatic(string $method, $args)
    {
        return static::getFacadeRoot()
            ->$method(
                ...$args
            );
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Concern\PdkInterface|null
     */
    public static function getPdkInstance(): ?PdkInterface
    {
        return self::$pdk;
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Base\Concern\PdkInterface $pdk
     *
     * @return void
     */
    public static function setPdkInstance(?PdkInterface $pdk): void
    {
        self::$pdk = $pdk;
    }

    /**
     * @return string
     */
    abstract protected static function getFacadeAccessor(): string;

    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidFacadeException
     */
    protected static function getFacadeRoot()
    {
        if (! static::$pdk) {
            throw new InvalidFacadeException('Pdk instance must be set to use facades.');
        }

        return static::$pdk->get(static::getFacadeAccessor());
    }
}

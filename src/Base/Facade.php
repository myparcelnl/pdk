<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base;

use MyParcelNL\Pdk\Base\Concern\PdkInterface;
use MyParcelNL\Pdk\Base\Exception\InvalidFacadeException;

abstract class Facade
{
    /**
     * @var \MyParcelNL\Pdk\Base\Concern\PdkInterface
     */
    protected static $pdk;

    /**
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidFacadeException
     */
    public static function __callStatic(string $method, mixed $args)
    {
        return static::getFacadeRoot()
            ->$method(
                ...$args
            );
    }

    /**
     * @internal
     */
    public static function getPdkInstance(): ?PdkInterface
    {
        return self::$pdk;
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Base\Concern\PdkInterface $pdk
     *
     * @internal
     */
    public static function setPdkInstance(?PdkInterface $pdk): void
    {
        self::$pdk = $pdk;
    }

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

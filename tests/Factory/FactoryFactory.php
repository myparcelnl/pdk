<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException;
use Throwable;

final class FactoryFactory
{
    /**
     * @param  class-string<Model|Collection> $model
     * @param  mixed                          ...$args
     *
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     */
    public static function create(string $model, ...$args): FactoryInterface
    {
        $factory = "{$model}Factory";

        try {
            return new $factory(...$args);
        } catch (Throwable $e) {
            throw new InvalidFactoryException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Factory;

use DI\ContainerBuilder;
use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Service\AbstractApiService;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Storage\StorageInterface;
use MyParcelNL\Sdk\src\Support\Arr;

final class PdkFactory
{
    private const REQUIRED_PROPERTIES = [
        'storage.default' => StorageInterface::class,
        'api'             => AbstractApiService::class,
    ];

    /**
     * @var \DI\Container
     */
    public $container;

    protected static $index =0;
    /**
     * @param  array $config
     *
     * @return \MyParcelNL\Pdk\Base\Pdk
     * @throws \Exception
     */
    public static function createPdk(array $config): Pdk
    {
        $items = Arr::dot($config);
        self::validate($items);

        $builder   = new ContainerBuilder();
        $container = $builder->build();

        foreach ($items as $key => $item) {
            $container->set($key, $item);
        }

        return new Pdk($container);
    }

    /**
     * @param  array $items
     *
     * @return void
     */
    public static function validate(array $items): void
    {
        $errors = [];

        foreach (self::REQUIRED_PROPERTIES as $property => $class) {
            if (! array_key_exists($property, $items)) {
                $errors[] = sprintf('Property %s missing from config', $property);
                continue;
            }

            if (! is_a($items[$property], $class)) {
                $errors[] = sprintf('Property %1$s must implement %2$s', $property, $class);
            }
        }

        if (! empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
    }
}

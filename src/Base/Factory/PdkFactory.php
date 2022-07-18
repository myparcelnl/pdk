<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Factory;

use DI\Container;
use DI\ContainerBuilder;
use InvalidArgumentException;
use MyParcelNL\Pdk\Api\MyParcelApiService;
use MyParcelNL\Pdk\Api\Service\ApiServiceInterface;
use MyParcelNL\Pdk\Base\Facade;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Storage\StorageInterface;
use MyParcelNL\Sdk\src\Support\Arr;
use MyParcelNL\Sdk\src\Support\Str;

class PdkFactory
{
    private const PROPERTY_INTERFACES = [
        'api'       => ApiServiceInterface::class,
        'storage.*' => StorageInterface::class,
    ];
    private const REQUIRED_PROPERTIES = [
        'storage.default',
    ];

    protected static $index = 0;

    /**
     * @var \DI\Container
     */
    public $container;

    /**
     * @param  array $config
     *
     * @return \MyParcelNL\Pdk\Base\Pdk
     * @throws \Exception
     */
    public static function create(array $config): Pdk
    {
        $container = self::setupContainer($config);
        $pdk       = new Pdk($container);

        Facade::setPdkInstance($pdk);

        return $pdk;
    }

    /**
     * @return string[]
     */
    private static function getDefaultConfig(): array
    {
        return [
            'api' => MyParcelApiService::class,
        ];
    }

    /**
     * @param  array  $items
     * @param  string $property
     * @param  string $interface
     *
     * @return null|string
     */
    private static function getInterfaceError(array $items, string $property, string $interface): ?string
    {
        $value = $items[$property];

        if (! in_array($interface, class_implements($value), true)) {
            return sprintf('Property %1$s must implement %2$s', $property, $interface);
        }

        return null;
    }

    /**
     * @param  array $config
     *
     * @return \DI\Container
     * @throws \Exception
     */
    private static function setupContainer(array $config): Container
    {
        $items = Arr::dot(array_replace_recursive(self::getDefaultConfig(), $config));
        self::validate($items);

        $builder   = new ContainerBuilder();
        $container = $builder->build();

        foreach ($items as $key => $item) {
            if (is_string($item) && class_exists($item)) {
                $instance = new $item();
            } else {
                $instance = $item;
            }

            $container->set($key, $instance);
        }
        return $container;
    }

    /**
     * @param  array $items
     *
     * @return void
     */
    private static function validate(array $items): void
    {
        $errors = [];

        foreach (self::REQUIRED_PROPERTIES as $property) {
            if (array_key_exists($property, $items)) {
                continue;
            }

            $errors[] = sprintf('Property %s missing from config', $property);
        }

        foreach (self::PROPERTY_INTERFACES as $property => $interface) {
            $itemsToValidate = $items;

            if (Str::contains($property, '*')) {
                $prop            = str_replace('.*', '', $property);
                $itemsToValidate = Arr::where($items, static function ($value, string $key) use ($prop) {
                    return Str::startsWith($key, $prop);
                });
            }

            if (! array_key_exists($property, $itemsToValidate)) {
                continue;
            }

            $errors[] = self::getInterfaceError($itemsToValidate, $property, $interface);
        }

        if (! empty(array_filter($errors))) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
    }
}

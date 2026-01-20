<?php
/** @noinspection PhpDocMissingThrowsInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory\Model;

use BadMethodCallException;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Tests\Factory\AbstractFactory;
use MyParcelNL\Pdk\Tests\Factory\Contract\CollectionFactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Contract\ModelFactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Exception\NotImplementedException;
use MyParcelNL\Pdk\Tests\Factory\FactoryFactory;
use MyParcelNL\Sdk\Support\Str;
use ReflectionClass;

abstract class AbstractModelFactory extends AbstractFactory implements ModelFactoryInterface
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @param  mixed $name
     * @param  mixed $arguments
     *
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (Str::startsWith($name, 'with')) {
            $attribute = Str::camel(Str::after($name, 'with'));
            $value     = $arguments[0];

            return $this->with([$attribute => $value]);
        }

        throw new BadMethodCallException(sprintf('Method %s does not exist', $name));
    }

    /**
     * @return \MyParcelNL\Pdk\Base\Model\Model
     */
    public function make(): Model
    {
        $model      = $this->getModel();
        $attributes = $this->resolveAttributes();

        $cacheKey = sprintf('%s::%s', $model, md5(json_encode($attributes)));

        if (! isset($this->cache[$cacheKey])) {
            $this->cache[$cacheKey] = new $model($attributes);
        }

        return $this->cache[$cacheKey];
    }

    /**
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\ModelFactoryInterface
     */
    public function store(): ModelFactoryInterface
    {
        $result = $this->make();

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->save($result);

        return $this;
    }

    /**
     * @param  array|\MyParcelNL\Pdk\Base\Support\Collection $data
     *
     * @return $this
     */
    public function with($data): ModelFactoryInterface
    {
        $this->attributes = $this->attributes->merge($data);

        return $this;
    }

    /**
     * @param  string $key
     *
     * @return \MyParcelNL\Pdk\Tests\Factory\Contract\CollectionFactoryInterface|\MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface
     * @throws \MyParcelNL\Pdk\Tests\Factory\Exception\InvalidFactoryException
     * @throws \ReflectionException
     */
    protected function createCollectionFactory(string $key)
    {
        $ref = new ReflectionClass($this->getModel());

        $props     = $ref->getDefaultProperties();
        $castClass = $props['casts'][$key] ?? null;

        if (! $castClass) {
            throw new BadMethodCallException(sprintf('No class found for %s', $key));
        }

        $factory = FactoryFactory::create($castClass);

        if (! $factory instanceof CollectionFactoryInterface) {
            throw new BadMethodCallException(sprintf('Factory for %s is not a collection factory', $key));
        }

        return $factory;
    }

    /**
     * @param  \MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface $factory
     *
     * @return $this
     */
    protected function from(FactoryInterface $factory): FactoryInterface
    {
        $result = $factory->make();

        return $this->with($result->toArrayWithoutNull());
    }

    /**
     * @param  string|null $key
     *
     * @return int
     */
    protected function getNextId(string $key = null): int
    {
        $key = $key ?? $this->getModel();

        return $this->state->getNextId($key);
    }

    /**
     * @param  \MyParcelNL\Pdk\Base\Model\Model $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        throw new NotImplementedException();
    }

    /**
     * @param  string        $key
     * @param  mixed         $items
     * @param  null|callable $callback
     *
     * @return $this
     */
    protected function withCollection(string $key, $items, ?callable $callback = null): ModelFactoryInterface
    {
        $factoryCallback = function ($factoryOrPlain) use ($callback) {
            if ($callback && $factoryOrPlain instanceof ModelFactoryInterface) {
                return $callback($factoryOrPlain);
            }

            return $factoryOrPlain;
        };

        if ($items instanceof CollectionFactoryInterface) {
            $items = $items->eachWith($factoryCallback);
        } elseif (is_array($items)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $collectionFactory = $this->createCollectionFactory($key)
                ->push(...$items);

            return $this->withCollection($key, $collectionFactory, $callback);
        } elseif (is_int($items)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $items = $this->createCollectionFactory($key)
                ->amount($items)
                ->eachWith($factoryCallback);
        }

        return $this->with([$key => $items]);
    }
}

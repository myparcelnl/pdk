<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory\Contract;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @template GModel of Model
 * @template GCollection of Collection<GModel>
 * @template GCollectionFactory of CollectionFactoryInterface<GModel>
 * @template GModelFactory of ModelFactoryInterface<GModel>
 */
interface CollectionFactoryInterface extends FactoryInterface
{
    /**
     * @param  int $amount
     *
     * @return GCollectionFactory
     */
    public function amount(int $amount): CollectionFactoryInterface;

    /**
     * @param  array|callable $data
     *
     * @return GCollectionFactory
     */
    public function eachWith($data): CollectionFactoryInterface;

    /**
     * @return class-string<GCollection>
     */
    public function getCollection(): string;

    /**
     * @return GCollection
     */
    public function make(): Collection;

    /**
     * @param  mixed $items
     *
     * @return GCollectionFactory
     */
    public function push(...$items): CollectionFactoryInterface;

    /**
     * @param  string $key
     * @param  mixed  $value
     *
     * @return GCollectionFactory
     */
    public function put(string $key, $value): CollectionFactoryInterface;
}

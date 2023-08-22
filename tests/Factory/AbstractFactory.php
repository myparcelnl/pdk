<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;

abstract class AbstractFactory implements FactoryInterface
{
    /**
     * @var Collection
     */
    protected $attributes;

    /**
     * @var \MyParcelNL\Pdk\Tests\Factory\SharedFactoryState
     */
    protected $state;

    public function __construct()
    {
        $this->state = Pdk::get(SharedFactoryState::class);

        $this->fromScratch();
        $this->createDefault();
    }

    /**
     * @return $this
     */
    public function fromScratch(): FactoryInterface
    {
        $this->attributes = new Collection();

        return $this;
    }

    /**
     * @return $this
     */
    protected function createDefault(): FactoryInterface
    {
        return $this;
    }

    /**
     * @param  mixed $attribute
     *
     * @return mixed
     */
    protected function resolveAttribute($attribute)
    {
        if ($attribute instanceof FactoryInterface) {
            return $attribute->make();
        }

        return $attribute;
    }

    /**
     * @return array
     */
    protected function resolveAttributes(): array
    {
        return array_map([$this, 'resolveAttribute'], $this->attributes->all());
    }
}

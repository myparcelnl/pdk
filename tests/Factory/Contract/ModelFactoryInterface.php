<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory\Contract;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @template T of Model
 */
interface ModelFactoryInterface extends FactoryInterface
{
    /**
     * @return class-string<T>
     */
    public function getModel(): string;

    /**
     * @return T
     */
    public function make(): Model;

    /**
     * @return self
     */
    public function store(): ModelFactoryInterface;

    /**
     * @param  array|\MyParcelNL\Pdk\Base\Support\Collection $data
     *
     * @return self
     */
    public function with($data): self;
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Factory\Contract;

interface FactoryInterface
{
    public function fromScratch(): FactoryInterface;

    /**
     * @return mixed
     */
    public function make();
}

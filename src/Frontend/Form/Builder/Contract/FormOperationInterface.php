<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Frontend\Form\Builder\FormCondition;

interface FormOperationInterface extends Arrayable
{
    public function createArray(): array;

    public function if(?string $target = null, ?callable $callable = null): FormCondition;

    public function on(?string $target): self;
}

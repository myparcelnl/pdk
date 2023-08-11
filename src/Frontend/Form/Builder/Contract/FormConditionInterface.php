<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;

interface FormConditionInterface extends Arrayable
{
    public function and(?string $target = null): self;

    public function eq($value): self;

    public function gt($value): self;

    public function gte($value): self;

    public function in(array $value): self;

    public function lt($value): self;

    public function lte($value): self;

    public function ne($value): self;

    public function nin(array $value): self;
}

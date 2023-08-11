<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

interface FormOperationBuilderInterface
{
    public function build(): array;
}

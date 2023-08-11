<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

interface FormSubOperationBuilderInterface extends FormOperationBuilderInterface
{
    public function getKey(): string;
}

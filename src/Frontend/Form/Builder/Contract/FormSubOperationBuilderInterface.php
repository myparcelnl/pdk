<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

interface FormSubOperationBuilderInterface extends FormOperationBuilderInterface
{
    /**
     * @return string
     */
    public function getKey(): string;

    /**
     * @param  scalar        $value
     * @param  null|callable $callback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetValueOperation
     */
    public function setValue($value, ?callable $callback = null): FormOperationInterface;
}

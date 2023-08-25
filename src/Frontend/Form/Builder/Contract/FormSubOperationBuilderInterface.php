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
     * @param  string        $prop
     * @param                $value
     * @param  null|callable $callback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface
     */
    public function setProp(string $prop, $value, ?callable $callback = null): FormOperationInterface;

    /**
     * @param  scalar        $value
     * @param  null|callable $callback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetValueOperation
     */
    public function setValue($value, ?callable $callback = null): FormOperationInterface;
}

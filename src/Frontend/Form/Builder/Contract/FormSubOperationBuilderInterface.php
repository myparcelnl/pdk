<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetPropOperation;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetValueOperation;

interface FormSubOperationBuilderInterface extends FormOperationBuilderInterface
{
    /**
     * @return array
     */
    public function createArray(): array;

    /**
     * @return string
     */
    public function getKey(): string;

    /**
     * @param  string        $prop
     * @param  mixed         $value
     * @param  null|callable $callback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetPropOperation
     */
    public function setProp(string $prop, $value, ?callable $callback = null): FormSetPropOperation;

    /**
     * @param  scalar        $value
     * @param  null|callable $callback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetValueOperation
     */
    public function setValue($value, ?callable $callback = null): FormSetValueOperation;
}

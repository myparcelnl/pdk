<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetPropOperation;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetValueOperation;

interface FormSubOperationBuilderInterface extends FormOperationBuilderInterface
{
    public function createArray(): array;

    public function getKey(): string;

    /**
     * @param  mixed         $value
     * @param  null|callable $callback
     */
    public function setProp(string $prop, $value, ?callable $callback = null): FormSetPropOperation;

    /**
     * @param  scalar        $value
     * @param  null|callable $callback
     */
    public function setValue($value, ?callable $callback = null): FormSetValueOperation;
}

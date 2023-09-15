<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetPropOperation;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetValueOperation;

abstract class AbstractFormSubOperationBuilder extends AbstractFormOperationBuilder implements
    FormSubOperationBuilderInterface
{
    /**
     * @param  mixed         $value
     * @param  null|callable $callback
     */
    public function setProp(string $prop, $value, ?callable $callback = null): FormSetPropOperation
    {
        return $this->addOperation(new FormSetPropOperation($this, $prop, $value), $callback);
    }

    /**
     * @param  scalar        $value
     * @param  null|callable $callback
     */
    public function setValue($value, ?callable $callback = null): FormSetValueOperation
    {
        return $this->addOperation(new FormSetValueOperation($this, $value), $callback);
    }
}

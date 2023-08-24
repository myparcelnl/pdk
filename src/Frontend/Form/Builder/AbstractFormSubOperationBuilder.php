<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetValueOperation;

abstract class AbstractFormSubOperationBuilder extends AbstractFormOperationBuilder implements
    FormSubOperationBuilderInterface
{
    /**
     * @param  scalar        $value
     * @param  null|callable $callback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetValueOperation
     */
    public function setValue($value, ?callable $callback = null): FormOperationInterface
    {
        return $this->addOperation(new FormSetValueOperation($this, $value), $callback);
    }
}

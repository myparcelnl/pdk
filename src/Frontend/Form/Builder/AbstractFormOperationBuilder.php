<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Frontend\Form\Builder\Concern\HasFormOperationBuilderParent;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Operation\FormSetValueOperation;

abstract class AbstractFormOperationBuilder implements FormOperationBuilderInterface
{
    use HasFormOperationBuilderParent;

    /**
     * @var \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface[]
     */
    protected $operations = [];

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

    /**
     * @param  FormOperationInterface $operation
     * @param  null|callable          $callback
     *
     * @return FormOperationInterface
     */
    protected function addOperation(
        FormOperationInterface $operation,
        ?callable              $callback = null
    ): FormOperationInterface {
        $this->operations[] = $operation;

        return $this->executeCallback($operation, $callback);
    }

    /**
     * @return array
     */
    protected function createArray(): array
    {
        return array_map(static function (FormOperationInterface $operation) {
            return $operation->toArray();
        }, $this->operations);
    }

    /**
     * @param  FormSubOperationBuilderInterface|FormOperationInterface $item
     * @param  null|callable                                           $callback
     *
     * @return FormSubOperationBuilderInterface|FormOperationInterface
     */
    protected function executeCallback($item, ?callable $callback = null)
    {
        if ($callback) {
            $callback($item);
        }

        return $item;
    }
}

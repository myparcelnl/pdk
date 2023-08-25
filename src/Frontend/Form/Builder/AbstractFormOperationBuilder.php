<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Frontend\Form\Builder\Concern\HasFormOperationBuilderParent;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\ChainableFormOperationBuilderInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface;
use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface;

abstract class AbstractFormOperationBuilder implements ChainableFormOperationBuilderInterface
{
    use HasFormOperationBuilderParent;

    /**
     * @var \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface[]
     */
    protected $operations = [];

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

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
     * @return array
     */
    public function createArray(): array
    {
        return array_map(static function (FormOperationInterface $operation) {
            return $operation->toArray();
        }, $this->operations);
    }

    /**
     * @template T of FormOperationInterface
     * @param  T             $operation
     * @param  null|callable $callback
     *
     * @return T
     */
    protected function addOperation(
        FormOperationInterface $operation,
        ?callable              $callback = null
    ): FormOperationInterface {
        $this->operations[] = $operation;

        return $this->executeCallback($operation, $callback);
    }

    /**
     * @template T of (FormOperationInterface|FormSubOperationBuilderInterface)
     * @param  T             $item
     * @param  null|callable $callback
     *
     * @return T
     */
    protected function executeCallback($item, ?callable $callback = null)
    {
        if ($callback) {
            $callback($item);
        }

        return $item;
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

interface RootFormOperationBuilderInterface extends FormOperationBuilderInterface
{
    /**
     * @param  null|callable $callback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface
     */
    public function afterUpdate(?callable $callback = null): FormSubOperationBuilderInterface;

    /**
     * @param  null|string     $target
     * @param  callable|scalar $valueOrCallback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface
     */
    public function readOnlyWhen(?string $target = null, $valueOrCallback = null): FormConditionInterface;

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface
     */
    public function visibleWhen(?string $target = null, $valueOrCallback = null): FormConditionInterface;
}


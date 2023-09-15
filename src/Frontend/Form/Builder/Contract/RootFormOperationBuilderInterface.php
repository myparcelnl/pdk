<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

interface RootFormOperationBuilderInterface extends FormOperationBuilderInterface
{
    /**
     * @param  null|callable $callback
     */
    public function afterUpdate(?callable $callback = null): FormSubOperationBuilderInterface;

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     */
    public function disabledWhen(?string $target = null, $valueOrCallback = null): FormConditionInterface;

    /**
     * @param  null|string     $target
     * @param  callable|scalar $valueOrCallback
     */
    public function readOnlyWhen(?string $target = null, $valueOrCallback = null): FormConditionInterface;

    /**
     * @param  null|string     $target
     * @param  scalar|callable $valueOrCallback
     */
    public function visibleWhen(?string $target = null, $valueOrCallback = null): FormConditionInterface;
}


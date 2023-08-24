<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

/**
 * @property \MyParcelNL\Pdk\Frontend\Form\Builder\FormCondition $if
 */
interface FormOperationInterface
{
    /**
     * @param  null|string   $target
     * @param  null|callable $callable
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface
     */
    public function if(?string $target = null, ?callable $callable = null): FormConditionInterface;

    /**
     * @param  null|string $target
     */
    public function on(?string $target): FormOperationInterface;
}

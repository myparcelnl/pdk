<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Contract;

use MyParcelNL\Pdk\Base\Contract\Arrayable;

/**
 * @property \MyParcelNL\Pdk\Frontend\Form\Builder\FormCondition $if
 */
interface FormOperationInterface extends Arrayable
{
    /**
     * @return array
     */
    public function createArray(): array;

    /**
     * @param  null|string   $target
     * @param  null|callable $callable
     *
     * @return \MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormConditionInterface
     */
    public function if(?string $target = null, ?callable $callable = null): FormConditionInterface;
}

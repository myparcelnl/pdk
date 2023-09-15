<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation\Concern;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface;

/**
 * @see \MyParcelNL\Pdk\Frontend\Form\Builder\Operation\Contract\FormOperationWithTargetInterface
 */
trait HasFormTarget
{
    /**
     * @param  null|string $target
     */
    public function on(?string $target): FormOperationInterface
    {
        $this->target = $target;

        return $this;
    }
}

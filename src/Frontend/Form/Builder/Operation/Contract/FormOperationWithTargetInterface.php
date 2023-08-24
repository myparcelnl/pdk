<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation\Contract;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormOperationInterface;

/**
 * @see \MyParcelNL\Pdk\Frontend\Form\Builder\Operation\Concern\HasFormTarget
 */
interface FormOperationWithTargetInterface extends FormOperationInterface
{
    /**
     * @param  null|string $target
     */
    public function on(?string $target): FormOperationInterface;
}

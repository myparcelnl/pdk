<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSubOperationBuilderInterface;

abstract class AbstractFormSubOperationBuilder extends AbstractFormOperationBuilder implements
    FormSubOperationBuilderInterface
{
    /**
     * @return array
     */
    public function build(): array
    {
        return $this->createArray();
    }
}

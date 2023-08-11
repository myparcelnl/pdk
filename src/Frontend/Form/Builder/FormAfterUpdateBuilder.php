<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder;

final class FormAfterUpdateBuilder extends AbstractFormSubOperationBuilder
{
    /**
     * @return string
     */
    public function getKey(): string
    {
        return '$afterUpdate';
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation;

final class FormReadOnlyWhenOperation extends AbstractFormOperation
{
    protected function getOperationKey(): string
    {
        return '$readOnlyWhen';
    }
}

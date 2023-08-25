<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Builder\Operation;

use MyParcelNL\Pdk\Frontend\Form\Builder\Contract\FormSingletonOperationInterface;

final class FormDisabledWhenOperation extends AbstractFormOperation implements FormSingletonOperationInterface
{
    protected function getOperationKey(): string
    {
        return '$disabledWhen';
    }
}

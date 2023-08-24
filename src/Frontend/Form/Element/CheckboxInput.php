<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Components;

final class CheckboxInput extends AbstractInteractiveElement
{
    protected function getComponent(): string
    {
        return Components::INPUT_CHECKBOX;
    }
}

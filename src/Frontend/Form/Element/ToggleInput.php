<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Components;

final class ToggleInput extends AbstractInteractiveElement
{
    protected function getComponent(): string
    {
        return Components::INPUT_TOGGLE;
    }
}

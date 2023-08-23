<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\Element\Concern\ElementBuilderWithOptionsInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\Concern\HasOptions;

final class SelectInput extends AbstractInteractiveInput implements ElementBuilderWithOptionsInterface
{
    use HasOptions;

    protected function getComponent(): string
    {
        return Components::INPUT_SELECT;
    }
}

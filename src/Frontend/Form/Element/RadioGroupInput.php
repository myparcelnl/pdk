<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\Form\Element;

use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\Element\Concern\HasOptions;
use MyParcelNL\Pdk\Frontend\Form\Element\Concern\OptionsInterface;

final class RadioGroupInput extends AbstractInteractiveInput implements OptionsInterface
{
    use HasOptions;

    protected function getComponent(): string
    {
        return Components::INPUT_RADIO_GROUP;
    }
}

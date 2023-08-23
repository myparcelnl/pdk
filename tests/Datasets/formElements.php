<?php

declare(strict_types=1);

use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\Element\CheckboxGroupInput;
use MyParcelNL\Pdk\Frontend\Form\Element\CheckboxInput;
use MyParcelNL\Pdk\Frontend\Form\Element\CodeEditorInput;
use MyParcelNL\Pdk\Frontend\Form\Element\CurrencyInput;
use MyParcelNL\Pdk\Frontend\Form\Element\DropOffInput;
use MyParcelNL\Pdk\Frontend\Form\Element\MultiSelectInput;
use MyParcelNL\Pdk\Frontend\Form\Element\NumberInput;
use MyParcelNL\Pdk\Frontend\Form\Element\RadioGroupInput;
use MyParcelNL\Pdk\Frontend\Form\Element\RadioInput;
use MyParcelNL\Pdk\Frontend\Form\Element\SelectInput;
use MyParcelNL\Pdk\Frontend\Form\Element\TextAreaInput;
use MyParcelNL\Pdk\Frontend\Form\Element\TextInput;
use MyParcelNL\Pdk\Frontend\Form\Element\TimeInput;
use MyParcelNL\Pdk\Frontend\Form\Element\ToggleInput;
use MyParcelNL\Pdk\Frontend\Form\Element\TriStateInput;

dataset('interactive elements', [
    'checkbox group input' => [CheckboxGroupInput::class, Components::INPUT_CHECKBOX_GROUP],
    'checkbox input'       => [CheckboxInput::class, Components::INPUT_CHECKBOX],
    'code editor input'    => [CodeEditorInput::class, Components::INPUT_CODE_EDITOR],
    'currency input'       => [CurrencyInput::class, Components::INPUT_CURRENCY],
    'drop off input'       => [DropOffInput::class, Components::INPUT_DROP_OFF],
    'multi select input'   => [MultiSelectInput::class, Components::INPUT_MULTI_SELECT],
    'number input'         => [NumberInput::class, Components::INPUT_NUMBER],
    'radio group input'    => [RadioGroupInput::class, Components::INPUT_RADIO_GROUP],
    'radio input'          => [RadioInput::class, Components::INPUT_RADIO],
    'select input'         => [SelectInput::class, Components::INPUT_SELECT],
    'text input'           => [TextInput::class, Components::INPUT_TEXT],
    'textarea input'       => [TextAreaInput::class, Components::INPUT_TEXTAREA],
    'time input'           => [TimeInput::class, Components::INPUT_TIME],
    'toggle input'         => [ToggleInput::class, Components::INPUT_TOGGLE],
    'tri state input'      => [TriStateInput::class, Components::INPUT_TRI_STATE],
]);

dataset('interactive elements with options', [
    'checkbox group input' => [CheckboxGroupInput::class, Components::INPUT_CHECKBOX_GROUP],
    'multi select input'   => [MultiSelectInput::class, Components::INPUT_MULTI_SELECT],
    'radio group input'    => [RadioGroupInput::class, Components::INPUT_RADIO_GROUP],
    'select input'         => [SelectInput::class, Components::INPUT_SELECT],
]);

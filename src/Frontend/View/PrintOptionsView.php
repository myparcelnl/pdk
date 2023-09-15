<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Frontend\Form\Element\MultiSelectInput;
use MyParcelNL\Pdk\Frontend\Form\Element\RadioGroupInput;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

final class PrintOptionsView extends NewAbstractSettingsView
{
    protected function addElements(): void
    {
        $this->formBuilder->add(
            (new RadioGroupInput(LabelSettings::OUTPUT))
                ->withOptions([
                    LabelSettings::OUTPUT_OPEN,
                    LabelSettings::OUTPUT_DOWNLOAD,
                ]),

            (new RadioGroupInput(LabelSettings::FORMAT))
                ->withOptions([
                    LabelSettings::FORMAT_A4,
                    LabelSettings::FORMAT_A6,
                ]),

            (new MultiSelectInput(LabelSettings::POSITION))
                ->withOptions([
                    LabelSettings::POSITION_1,
                    LabelSettings::POSITION_2,
                    LabelSettings::POSITION_3,
                    LabelSettings::POSITION_4,
                ])
                ->visibleWhen(LabelSettings::FORMAT, LabelSettings::FORMAT_A4)
        );
    }

    protected function getPrefix(): string
    {
        return 'print';
    }
}

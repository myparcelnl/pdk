<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Frontend\Form\Builder\FormOperationBuilder;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

class PrintOptionsView extends AbstractSettingsView
{
    /**
     * @return null|array
     */
    protected function createElements(): ?array
    {
        return [
            new InteractiveElement(LabelSettings::OUTPUT, Components::INPUT_RADIO_GROUP, [
                'options' => $this->createSelectOptions(LabelSettings::OUTPUT, [
                    LabelSettings::OUTPUT_OPEN,
                    LabelSettings::OUTPUT_DOWNLOAD,
                ]),
            ]),
            new InteractiveElement(LabelSettings::FORMAT, Components::INPUT_RADIO_GROUP, [
                'options' => $this->createSelectOptions(LabelSettings::FORMAT, [
                    LabelSettings::FORMAT_A4,
                    LabelSettings::FORMAT_A6,
                ]),
            ]),
            (new InteractiveElement(LabelSettings::POSITION, Components::INPUT_MULTI_SELECT, [
                'options' => $this->createSelectOptions(LabelSettings::POSITION, [
                    LabelSettings::POSITION_1,
                    LabelSettings::POSITION_2,
                    LabelSettings::POSITION_3,
                    LabelSettings::POSITION_4,
                ]),
            ]))->builder(function (FormOperationBuilder $builder) {
                $builder->visibleWhen(LabelSettings::FORMAT, LabelSettings::FORMAT_A4);
            }),
        ];
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return 'print';
    }
}

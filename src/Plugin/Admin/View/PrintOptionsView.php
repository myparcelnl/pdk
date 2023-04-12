<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Admin\View;

use MyParcelNL\Pdk\Frontend\Collection\FormElementCollection;
use MyParcelNL\Pdk\Frontend\Form\Components;
use MyParcelNL\Pdk\Frontend\Form\InteractiveElement;
use MyParcelNL\Pdk\Frontend\Settings\View\AbstractSettingsView;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

class PrintOptionsView extends AbstractSettingsView
{
    /**
     * @return \MyParcelNL\Pdk\Frontend\Collection\FormElementCollection
     */
    protected function createElements(): FormElementCollection
    {
        return new FormElementCollection([
            new InteractiveElement(LabelSettings::OUTPUT, Components::INPUT_SELECT, [
                'options' => $this->createSelectOptions(LabelSettings::OUTPUT, [
                    LabelSettings::OUTPUT_OPEN,
                    LabelSettings::OUTPUT_DOWNLOAD,
                ]),
            ]),
            new InteractiveElement(LabelSettings::FORMAT, Components::INPUT_SELECT, [
                'options' => $this->createSelectOptions(LabelSettings::FORMAT, [
                    LabelSettings::FORMAT_A4,
                    LabelSettings::FORMAT_A6,
                ]),
            ]),
            new InteractiveElement(LabelSettings::POSITION, Components::INPUT_SELECT, [
                '$visibleWhen' => [LabelSettings::FORMAT => LabelSettings::FORMAT_A4],
                'options'      => $this->createSelectOptions(LabelSettings::POSITION, [
                    LabelSettings::POSITION_1,
                    LabelSettings::POSITION_2,
                    LabelSettings::POSITION_3,
                    LabelSettings::POSITION_4,
                ]),
            ]),
        ]);
    }

    /**
     * @return string
     */
    protected function getSettingsId(): string
    {
        return 'print';
    }
}

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
    protected function getElements(): FormElementCollection
    {
        return new FormElementCollection([
            new InteractiveElement(LabelSettings::OUTPUT, Components::INPUT_SELECT, [
                'options' => $this->toSelectOptions([
                    LabelSettings::OUTPUT_OPEN     => 'settings_label_output_option_open',
                    LabelSettings::OUTPUT_DOWNLOAD => 'settings_label_output_option_download',
                ]),
            ]),
            new InteractiveElement(LabelSettings::FORMAT, Components::INPUT_SELECT, [
                'options' => $this->toSelectOptions([
                    LabelSettings::FORMAT_A4 => 'settings_label_format_option_a4',
                    LabelSettings::FORMAT_A6 => 'settings_label_format_option_a6',
                ]),
            ]),
            new InteractiveElement(LabelSettings::POSITION, Components::INPUT_SELECT, [
                'options'      => $this->toSelectOptions([
                    LabelSettings::POSITION_1 => 'settings_label_position_option_1',
                    LabelSettings::POSITION_2 => 'settings_label_position_option_2',
                    LabelSettings::POSITION_3 => 'settings_label_position_option_3',
                    LabelSettings::POSITION_4 => 'settings_label_position_option_4',
                ]),
                '$visibleWhen' => [LabelSettings::FORMAT => LabelSettings::FORMAT_A4],
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

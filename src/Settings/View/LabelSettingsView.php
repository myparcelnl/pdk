<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Form\Model\Input\ToggleInput;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class LabelSettingsView extends AbstractView
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function getFields(): Collection
    {
        return new Collection([
            [
                'class'       => TextInput::class,
                'name'        => LabelSettings::DESCRIPTION,
                'label'       => 'settings_label_description',
                'description' => 'settings_label_description_description',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => LabelSettings::FORMAT,
                'label'   => 'settings_label_format',
                'options' => [
                    LabelSettings::FORMAT_A4 => 'settings_label_format_option_a4',
                    LabelSettings::FORMAT_A6 => 'settings_label_format_option_a6',
                ],
            ],
            [
                'class'   => SelectInput::class,
                'name'    => LabelSettings::POSITION,
                'label'   => 'settings_label_position',
                'options' => [
                    LabelSettings::POSITION_1 => 'settings_label_position_option_1',
                    LabelSettings::POSITION_2 => 'settings_label_position_option_2',
                    LabelSettings::POSITION_3 => 'settings_label_position_option_3',
                    LabelSettings::POSITION_4 => 'settings_label_position_option_4',
                ],
            ],
            [
                'class'   => SelectInput::class,
                'name'    => LabelSettings::OUTPUT,
                'label'   => 'settings_label_output',
                'options' => [
                    LabelSettings::OUTPUT_OPEN     => 'settings_label_output_option_open',
                    LabelSettings::OUTPUT_DOWNLOAD => 'settings_label_output_option_download',
                ],
            ],
            [
                'class' => ToggleInput::class,
                'name'  => LabelSettings::PROMPT,
                'label' => 'settings_label_prompt',
            ],
        ]);
    }
}

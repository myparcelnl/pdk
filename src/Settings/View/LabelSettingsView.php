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
                'class' => TextInput::class,
                'name'  => LabelSettings::LABEL_DESCRIPTION,
                'label' => 'Label description',
                'desc'  => 'The maximum length is 45 characters. You can add the following variables to the description',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => LabelSettings::LABEL_SIZE,
                'label'   => 'Default label size',
                'options' => [
                    'a4' => 'A4',
                    'a6' => 'A6',
                ],
            ],
            [
                'class'   => SelectInput::class,
                'name'    => LabelSettings::DEFAULT_POSITION,
                'label'   => 'Default label position',
                'options' => [
                    1 => 'Top left',
                    2 => 'Top right',
                    3 => 'Bottom left',
                    4 => 'Bottom right',
                ],
            ],
            [
                'class'   => SelectInput::class,
                'name'    => LabelSettings::LABEL_OPEN_DOWNLOAD,
                'label'   => 'Open or download label',
                'options' => [
                    true  => 'Open',
                    false => 'Download',
                ],
            ],
            [
                'class' => ToggleInput::class,
                'name'  => LabelSettings::PROMPT_POSITION,
                'label' => 'Prompt for label position',
            ],
        ]);
    }
}

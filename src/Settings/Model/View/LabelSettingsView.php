<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model\View;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Form\Model\Input\RadioButtonInput;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Form\Model\Input\ToggleInput;

/**
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput        $labelDescription
 * @property \MyParcelNL\Pdk\Form\Model\Input\RadioButtonInput $labelFormat
 * @property \MyParcelNL\Pdk\Form\Model\Input\                 $defaultPosition
 * @property \MyParcelNL\Pdk\Form\Model\Input\SelectInput      $labelOpenDownload
 * @property \MyParcelNL\Pdk\Form\Model\Input\SelectInput      $promptPosition
 */
class LabelSettingsView extends Model
{
    public function __construct(array $data = null)
    {
        $this->attributes['labelDescription']  = [
            'name'  => 'labelDescription',
            'type'  => 'text',
            'label' => 'Label description',
            'desc'  => 'The maximum length is 45 characters. You can add the following variables to the description',
        ];
        $this->attributes['labelSize']         = [
            'name'    => 'labelSize',
            'type'    => 'select',
            'label'   => 'Default label size',
            'options' => [
                'a4' => 'A4',
                'a6' => 'A6',
            ],
        ];
        $this->attributes['defaultPosition']   = [
            'name'    => 'defaultPosition',
            'type'    => 'select',
            'label'   => 'Default label position',
            'options' => [
                1 => 'Top left',
                2 => 'Top right',
                3 => 'Bottom left',
                4 => 'Bottom right',
            ],
        ];
        $this->attributes['labelOpenDownload'] = [
            'name'    => 'labelOpenDownload',
            'type'    => 'select',
            'label'   => 'Open or download label',
            'options' => [
                true  => 'Open',
                false => 'Download',
            ],
        ];
        $this->attributes['promptPosition']    = [
            'name'  => 'promptPosition',
            'type'  => 'toggle',
            'label' => 'Prompt for label position',
        ];

        $this->casts['labelDescription']  = TextInput::class;
        $this->casts['labelSize']         = SelectInput::class;
        $this->casts['defaultPosition']   = SelectInput::class;
        $this->casts['labelOpenDownload'] = SelectInput::class;
        $this->casts['promptPosition']    = ToggleInput::class;

        parent::__construct($data);
    }
}

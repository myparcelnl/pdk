<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model\View;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Form\Model\Input\RadioButtonInput;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;

/**
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput        $labelDescription
 * @property \MyParcelNL\Pdk\Form\Model\Input\RadioButtonInput $labelFormat
 * @property \MyParcelNL\Pdk\Form\Model\Input\                 $defaultPosition
 * @property \MyParcelNL\Pdk\Form\Model\Input\SelectInput      $labelOpenDownload
 * @property \MyParcelNL\Pdk\Form\Model\Input\SelectInput      $promptPosition
 */
class LabelSettingsView extends Model
{
    protected $attributes = [
        'labelDescription'  => TextInput::class,
        'labelFormat'       => RadioButtonInput::class,
        'defaultPosition'   => null,
        'labelOpenDownload' => SelectInput::class,
        'promptPosition'    => SelectInput::class,
    ];

    protected $casts      = [
        'labelDescription'  => TextInput::class,
        'labelFormat'       => RadioButtonInput::class,
        'defaultPosition'   => null,
        'labelOpenDownload' => SelectInput::class,
        'promptPosition'    => SelectInput::class,
    ];
}

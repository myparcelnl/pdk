<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $labelDescription
 * @property string $labelFormat
 * @property string $defaultPosition
 * @property string $labelOpenDownload
 * @property bool   $promptPosition
 */
class LabelSettings extends Model
{
    protected $attributes = [
        'labelDescription'  => null,
        'labelSize'         => null,
        'defaultPosition'   => null,
        'labelOpenDownload' => null,
        'promptPosition'    => false,
    ];

    protected $casts      = [
        'labelDescription'  => 'string',
        'labelSize'         => 'string',
        'defaultPosition'   => 'string',
        'labelOpenDownload' => 'string',
        'promptPosition'    => 'bool',
    ];
}

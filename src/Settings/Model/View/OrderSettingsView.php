<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model\View;

use MyParcelNL\Pdk\Base\Model\Model;

class OrderSettingsView extends Model
{
    protected $attributes = [
        'statusOnLabelCreate'    => null,
        'statusWhenLabelScanned' => null,
        'statusWhenDelivered'    => null,
    ];

    protected $casts      = [
        'statusOnLabelCreate'    => 'string',
        'statusWhenLabelScanned' => 'string',
        'statusWhenDelivered'    => 'string',
    ];
}

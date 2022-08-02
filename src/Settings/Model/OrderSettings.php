<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $statusOnLabelCreate
 * @property string $statusWhenLabelScanned
 * @property string $statusWhenDelivered
 */
class OrderSettings extends Model
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

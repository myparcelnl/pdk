<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Account\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string $carrier
 * @property string      $defaultCutoffTime
 * @property string      $defaultDropOffPoint
 * @property string      $defaultDropOffPointIdentifier
 * @property string      $mondayCutoffTime
 */
class ShopCarrierConfiguration extends Model
{
    public    $attributes = [
        'carrier'                       => null,
        'defaultCutoffTime'             => null,
        'defaultDropOffPoint'           => null,
        'defaultDropOffPointIdentifier' => null,
        'mondayCutoffTime'              => null,
    ];

    protected $casts      = [
        'carrier'                       => 'string',
        'defaultCutoffTime'             => 'string',
        'defaultDropOffPoint'           => 'string',
        'defaultDropOffPointIdentifier' => 'string',
        'mondayCutoffTime'              => 'string',
    ];
}


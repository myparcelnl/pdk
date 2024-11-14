<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property null|string $orderLineIdentifier
 * @property int         $quantity
 */
class ShippedItem extends Model
{
    public    $attributes = [
        'orderLineIdentifier' => null,
        'quantity'            => 1,
    ];

    protected $casts      = [
        'orderLineIdentifier' => 'string',
        'quantity'            => 'int',
    ];
}

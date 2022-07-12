<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

/**
 * @property int    $amount
 * @property string $currency
 */
class Currency extends Model
{
    protected $attributes = [
        'amount'   => 0,
        'currency' => 'EUR',
    ];

    protected $casts      = [
        'amount'   => 'int',
        'currency' => 'string',
    ];
}

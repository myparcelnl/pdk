<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property string $id
 * @property string $translation
 * @property float  $amount
 */
class PdkCartFee extends Model
{
    public    $attributes = [
        'id'          => 'string',
        'translation' => 'string',
        'amount'      => 'float',
    ];

    protected $casts      = [
        'id'          => 'string',
        'translation' => 'string',
        'amount'      => 'float',
    ];
}

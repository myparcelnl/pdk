<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;

/**
 * @property int                                 $amount
 * @property null|string                         $classification
 * @property null|string                         $country
 * @property null|string                         $description
 * @property \MyParcelNL\Pdk\Base\Model\Currency $itemValue
 * @property int                                 $weight
 */
class CustomsDeclarationItem extends Model
{
    public const DEFAULT_CLASSIFICATION = '0000';

    protected $attributes = [
        'amount'         => 1,
        'classification' => self::DEFAULT_CLASSIFICATION,
        'country'        => null,
        'description'    => null,
        'itemValue'      => Currency::class,
        'weight'         => 0,
    ];

    protected $casts      = [
        'amount'         => 'int',
        'classification' => 'string',
        'country'        => 'string',
        'description'    => 'string',
        'itemValue'      => Currency::class,
        'weight'         => 'int',
    ];
}

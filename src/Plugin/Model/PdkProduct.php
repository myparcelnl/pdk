<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;

/**
 * @property null|string                                    $externalIdentifier
 * @property null|string                                    $sku
 * @property null|string                                    $ean
 * @property null|bool                                      $isDeliverable
 * @property null|string                                    $name
 * @property null|\MyParcelNL\Pdk\Base\Model\Currency       $price
 * @property int                                            $weight
 * @property \MyParcelNL\Pdk\Settings\Model\ProductSettings $settings
 */
class PdkProduct extends Model
{
    /**
     * @var array
     */
    protected $attributes = [
        'externalIdentifier' => null,
        'sku'                => null,
        'ean'                => null,
        'isDeliverable'      => null,
        'name'               => null,
        'price'              => null,
        'weight'             => 0,
        'settings'           => ProductSettings::class,
    ];

    /**
     * @var array
     */
    protected $casts = [
        'externalIdentifier' => 'string',
        'sku'                => 'string',
        'ean'                => 'string',
        'isDeliverable'      => 'bool',
        'name'               => 'string',
        'price'              => Currency::class,
        'weight'             => 'int',
        'settings'           => ProductSettings::class,
    ];
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;

/**
 * @property null|string                                    $sku
 * @property null|string                                    $ean
 * @property null|string                                    $name
 * @property int                                            $weight
 * @property \MyParcelNL\Pdk\Settings\Model\ProductSettings $settings
 */
class PdkProduct extends Model
{
    /**
     * @var array
     */
    protected $attributes = [
        'sku'      => null,
        'ean'      => null,
        'name'     => null,
        'weight'   => 0,
        'settings' => ProductSettings::class,
    ];

    /**
     * @var array
     */
    protected $casts = [
        'sku'      => 'string',
        'ean'      => 'string',
        'name'     => 'string',
        'weight'   => 'int',
        'settings' => ProductSettings::class,
    ];
}

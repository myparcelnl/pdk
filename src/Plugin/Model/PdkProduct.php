<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;

/**
 * @property null|string                                    $sku
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
        'weight'   => 0,
        'settings' => ProductSettings::class,
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'sku'      => 'string',
        'weight'   => 'int',
        'settings' => ProductSettings::class,
    ];
}

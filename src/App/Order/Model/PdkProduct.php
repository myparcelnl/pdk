<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Settings\Model\AbstractSettingsModel;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;

/**
 * @property null|string                                     $externalIdentifier
 * @property null|string                                     $sku
 * @property null|string                                     $ean
 * @property null|bool                                       $isDeliverable
 * @property null|string                                     $name
 * @property null|\MyParcelNL\Pdk\Base\Model\Currency        $price
 * @property int                                             $weight
 * @property int                                             $length
 * @property int                                             $height
 * @property int                                             $width
 * @property \MyParcelNL\Pdk\Settings\Model\ProductSettings  $settings
 * @property null|\MyParcelNL\Pdk\App\Order\Model\PdkProduct $parent
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
        'length'             => 0,
        'width'              => 0,
        'height'             => 0,
        'settings'           => ProductSettings::class,
        'parent'             => null,
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
        'length'             => 'int',
        'width'              => 'int',
        'height'             => 'int',
        'settings'           => ProductSettings::class,
        'parent'             => self::class,
    ];

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function getMergedSettings(): ProductSettings
    {
        if (! $this->parent instanceof self) {
            return $this->settings;
        }

        $settings = $this->parent->getMergedSettings();

        foreach ($settings->getAttributes() as $key => $value) {
            if (AbstractSettingsModel::TRISTATE_VALUE_DEFAULT === (int) $value
                || '' === $value) {
                $settings->setAttribute($key, $this->settings->getAttribute($key));
            }
        }

        return $settings;
    }
}

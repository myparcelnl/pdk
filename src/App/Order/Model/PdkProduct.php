<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Settings\Model\ProductSettings;
use MyParcelNL\Pdk\Types\Contract\TriStateServiceInterface;
use MyParcelNL\Pdk\Types\Service\TriStateService;

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
 * @property \MyParcelNL\Pdk\Settings\Model\ProductSettings  $mergedSettings
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
        'isDeliverable'      => true,
        'name'               => null,
        'price'              => null,
        'weight'             => 0,
        'length'             => 0,
        'width'              => 0,
        'height'             => 0,
        'settings'           => ProductSettings::class,
        'mergedSettings'     => null,
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
        'mergedSettings'     => ProductSettings::class,
        'parent'             => self::class,
    ];

    /**
     * @var null|ProductSettings
     */
    private $cachedMergedSettings;

    /**
     * @return array
     */
    public function toStorableArray(): array
    {
        return [
            'externalIdentifier' => $this->externalIdentifier,
            'settings'           => $this->settings->toStorableArray(),
        ];
    }

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\ProductSettings
     */
    protected function getMergedSettingsAttribute(): ProductSettings
    {
        if (! isset($this->cachedMergedSettings)) {
            $this->cachedMergedSettings = $this->resolveMergedSettings();
        }

        return $this->cachedMergedSettings;
    }

    /**
     * Clear the cached merged settings on update.
     *
     * @param  \MyParcelNL\Pdk\Settings\Model\ProductSettings|array $settings
     *
     * @return $this
     * @noinspection PhpUnused
     */
    protected function setSettingsAttribute($settings): self
    {
        $this->cachedMergedSettings   = null;
        $this->attributes['settings'] = $settings;

        return $this;
    }

    /**
     * @return \MyParcelNL\Pdk\Settings\Model\ProductSettings
     */
    private function resolveMergedSettings(): ProductSettings
    {
        $settings = clone $this->settings;

        if (! $this->parent instanceof self) {
            return $settings;
        }

        $triStateService = Pdk::get(TriStateServiceInterface::class);

        foreach ($settings->getAttributes() as $key => $value) {
            $coerced = $triStateService->coerce($settings->getAttribute($key));

            if ($coerced === '-1') {
                $coerced = (int) $coerced;
            }

            if (TriStateService::INHERIT !== $coerced) {
                continue;
            }

            $settings->setAttribute($key, $this->parent->mergedSettings->getAttribute($key));
        }

        return $settings;
    }
}

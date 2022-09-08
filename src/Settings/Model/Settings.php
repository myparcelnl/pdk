<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Settings\Collection\CarrierSettingsCollection;

/**
 * @property GeneralSettings           $general
 * @property OrderSettings             $order
 * @property LabelSettings             $label
 * @property CustomsSettings           $customs
 * @property CarrierSettingsCollection $carrier
 */
class Settings extends Model
{
    public $attributes = [
        GeneralSettings::ID  => GeneralSettings::class,
        OrderSettings::ID    => OrderSettings::class,
        LabelSettings::ID    => LabelSettings::class,
        CustomsSettings::ID  => CustomsSettings::class,
        CarrierSettings::ID  => CarrierSettingsCollection::class,
        CheckoutSettings::ID => CheckoutSettings::class,
    ];

    /**
     * @param  null|array $data
     *
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function __construct(?array $data = null)
    {
        $this->casts = $this->attributes;
        parent::__construct($data);

        foreach (array_keys($this->getAttributes()) as $key) {
            $this->guarded[$key] = $this->getAttribute($key);
        }
    }
}

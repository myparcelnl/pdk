<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Settings\Collection\SettingsModelCollection;

/**
 * @property AccountSettings                           $account
 * @property GeneralSettings                           $general
 * @property OrderSettings                             $order
 * @property LabelSettings                             $label
 * @property CustomsSettings                           $customs
 * @property CheckoutSettings                          $checkout
 * @property SettingsModelCollection|CarrierSettings[] $carrier
 */
class Settings extends Model
{
    public $attributes = [
        AccountSettings::ID  => AccountSettings::class,
        GeneralSettings::ID  => GeneralSettings::class,
        OrderSettings::ID    => OrderSettings::class,
        LabelSettings::ID    => LabelSettings::class,
        CustomsSettings::ID  => CustomsSettings::class,
        CheckoutSettings::ID => CheckoutSettings::class,
        CarrierSettings::ID  => SettingsModelCollection::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $this->casts = $this->attributes;
        parent::__construct($data);

        foreach (array_keys($this->getAttributes()) as $key) {
            $this->guarded[$key] = $this->{$key};
        }

        $this->carrier->id = CarrierSettings::ID;
        $this->carrier->setCast(CarrierSettings::class);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\Input\Select\DropOffDaySelectInput;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Form\Model\Input\ToggleInput;
use MyParcelNL\Pdk\Settings\Model\CarrierSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CarrierSettingsView extends AbstractView
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function getFields(): Collection
    {
        return new Collection([
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_DELIVERY_OPTIONS,
                'label' => 'settings_carrier_allow_delivery_options',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_EVENING_DELIVERY,
                'label' => 'settings_carrier_allow_evening_delivery',
            ],
            [
                'class'       => ToggleInput::class,
                'name'        => CarrierSettings::ALLOW_MONDAY_DELIVERY,
                'label'       => 'settings_carrier_allow_monday_delivery',
                'description' => 'settings_carrier_allow_monday_delivery_description',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_MORNING_DELIVERY,
                'label' => 'settings_carrier_allow_morning_delivery',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_ONLY_RECIPIENT,
                'label' => 'settings_carrier_allow_only_recipient',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_PICKUP_LOCATIONS,
                'label' => 'settings_carrier_allow_pickup_locations',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
                'label' => 'settings_carrier_allow_same_day_delivery',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_SATURDAY_DELIVERY,
                'label' => 'settings_carrier_allow_saturday_delivery',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_SIGNATURE,
                'label' => 'settings_carrier_allow_signature',
            ],
            [
                'class' => DropOffDaySelectInput::class,
                'name'  => CarrierSettings::DROP_OFF_POSSIBILITIES,
                'label' => 'settings_carrier_drop_off_days',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::FEATURE_SHOW_DELIVERY_DATE,
                'label' => 'settings_carrier_feature_show_delivery_date',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_EVENING_DELIVERY,
                'label' => 'settings_carrier_price_evening_delivery',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_MORNING_DELIVERY,
                'label' => 'settings_carrier_price_morning_delivery',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_ONLY_RECIPIENT,
                'label' => 'settings_carrier_price_only_recipient',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
                'label' => 'settings_carrier_price_package_type_digital_stamp',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
                'label' => 'settings_carrier_price_package_type_mailbox',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_PICKUP,
                'label' => 'settings_carrier_price_pickup',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_SAME_DAY_DELIVERY,
                'label' => 'settings_carrier_price_same_day_delivery',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_SIGNATURE,
                'label' => 'settings_carrier_price_signature',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_STANDARD_DELIVERY,
                'label' => 'settings_carrier_price_standard_delivery',
            ],
            [
                'class' => SelectInput::class,
                'name'  => CarrierSettings::DEFAULT_PACKAGE_TYPE,
                'label' => 'settings_carrier_default_package_type',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_AGE_CHECK,
                'label' => 'settings_carrier_export_age_check',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_INSURANCE,
                'label' => 'settings_carrier_export_insurance',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::EXPORT_INSURANCE_AMOUNT,
                'label' => 'settings_carrier_export_insurance_amount',
            ],
            [
                'class' => SelectInput::class,
                'name'  => CarrierSettings::EXPORT_INSURANCE_UP_TO,
                'label' => 'settings_carrier_export_insurance_up_to',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_INSURANCE_BELGIUM,
                'label' => 'settings_carrier_allow_insurance_belgium',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_LARGE_FORMAT,
                'label' => 'settings_carrier_export_large_format',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_ONLY_RECIPIENT,
                'label' => 'settings_carrier_export_only_recipient',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_RETURN_SHIPMENTS,
                'label' => 'settings_carrier_export_return_shipments',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_SIGNATURE,
                'label' => 'settings_carrier_export_signature',
            ],
            [
                'class' => SelectInput::class,
                'name'  => CarrierSettings::DIGITAL_STAMP_DEFAULT_WEIGHT,
                'label' => 'settings_carrier_digital_stamp_default_weight',
            ],
        ]);
    }
}

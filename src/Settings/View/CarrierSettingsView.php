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
                'label' => 'Allow delivery options',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_EVENING_DELIVERY,
                'label' => 'Allow evening delivery',
            ],
            [
                'class'       => ToggleInput::class,
                'name'        => CarrierSettings::ALLOW_MONDAY_DELIVERY,
                'label'       => 'Allow monday delivery',
                'description' => 'Monday delivery is only possible when the package is delivered before 15.00 on Saturday at the designated PostNL locations. 
                 Note: To activate Monday delivery value 6 must be given with dropOffDays and value 1 must be given by monday_delivery. 
                 On Saturday the cutoffTime must be before 15:00 (14:30 recommended) so that Monday will be shown.',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_MORNING_DELIVERY,
                'label' => 'Allow morning delivery',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_ONLY_RECIPIENT,
                'label' => 'Allow only recipient',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_PICKUP_LOCATIONS,
                'label' => 'Allow pickup points',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_SAME_DAY_DELIVERY,
                'label' => 'Allow same day delivery',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_SATURDAY_DELIVERY,
                'label' => 'Allow saturday delivery',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::ALLOW_SIGNATURE,
                'label' => 'Allow signature',
            ],
            [
                'class' => DropOffDaySelectInput::class,
                'name'  => CarrierSettings::DROP_OFF_POSSIBILITIES,
                'label' => 'Drop off days',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::FEATURE_SHOW_DELIVERY_DATE,
                'label' => 'Show delivery date',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_EVENING_DELIVERY,
                'label' => 'Price evening delivery',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_MORNING_DELIVERY,
                'label' => 'Price morning delivery',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_ONLY_RECIPIENT,
                'label' => 'Price only recipient',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_PACKAGE_TYPE_DIGITAL_STAMP,
                'label' => 'Price package type digital stamp',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_PACKAGE_TYPE_MAILBOX,
                'label' => 'Price package type mailbox',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_PICKUP,
                'label' => 'Price pickup',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_SAME_DAY_DELIVERY,
                'label' => 'Price same day delivery',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_SIGNATURE,
                'label' => 'Price signature',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::PRICE_STANDARD_DELIVERY,
                'label' => 'Price standard delivery',
            ],
            [
                'class' => SelectInput::class,
                'name'  => CarrierSettings::DEFAULT_PACKAGE_TYPE,
                'label' => 'Default package type',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_AGE_CHECK,
                'label' => 'Age check 18+',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_INSURED,
                'label' => 'Insure shipment',
            ],
            [
                'class' => TextInput::class,
                'name'  => CarrierSettings::EXPORT_INSURED_AMOUNT,
                'label' => 'Insure from price',
            ],
            [
                'class' => SelectInput::class,
                'name'  => CarrierSettings::EXPORT_INSURED_AMOUNT_MAX,
                'label' => 'Insure to maximum',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_INSURED_FOR_BE,
                'label' => 'Insure shipments to Belgium',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_EXTRA_LARGE_FORMAT,
                'label' => 'Extra large package',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_ONLY_RECIPIENT,
                'label' => 'Only home address',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_RETURN_SHIPMENTS,
                'label' => 'Return when not delivered',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CarrierSettings::EXPORT_SIGNATURE,
                'label' => 'Signature before delivery',
            ],
            [
                'class' => SelectInput::class,
                'name'  => CarrierSettings::DIGITAL_STAMP_DEFAULT_WEIGHT,
                'label' => 'Default weight digital stamp',
            ],
        ]);
    }
}

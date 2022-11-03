<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Form\Model\Input\ToggleInput;
use MyParcelNL\Pdk\Settings\Model\CheckoutSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class CheckoutSettingsView extends AbstractView
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function getFields(): Collection
    {
        return new Collection([
            [
                'class' => ToggleInput::class,
                'name'  => CheckoutSettings::USE_SEPARATE_ADDRESS_FIELDS,
                'label' => 'settings_checkout_use_separate_address_fields',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => CheckoutSettings::PRICE_TYPE,
                'label'   => 'settings_checkout_price_type',
                'options' => [],
            ],
            [
                'class' => ToggleInput::class,
                'name'  => CheckoutSettings::SHOW_DELIVERY_DAY,
                'label' => 'settings_checkout_show_delivery_day',
            ],
            [
                'class'       => TextInput::class,
                'name'        => CheckoutSettings::STRING_DELIVERY,
                'label'       => 'settings_checkout_string_delivery',
                'description' => 'settings_checkout_string_delivery_description',
            ],
            [
                'class'       => TextInput::class,
                'name'        => CheckoutSettings::STRING_STANDARD_DELIVERY,
                'label'       => 'settings_checkout_string_standard_delivery',
                'description' => 'settings_checkout_string_standard_delivery_description',
            ],
            [
                'class'       => TextInput::class,
                'name'        => CheckoutSettings::STRING_MORNING_DELIVERY,
                'label'       => 'settings_checkout_string_morning_delivery',
                'description' => 'settings_checkout_string_standard_delivery_description',
            ],
            [
                'class'       => TextInput::class,
                'name'        => CheckoutSettings::STRING_EVENING_DELIVERY,
                'label'       => 'settings_checkout_string_evening_delivery',
                'description' => 'settings_checkout_string_standard_delivery_description',
            ],
            [
                'class'       => TextInput::class,
                'name'        => CheckoutSettings::STRING_SATURDAY_DELIVERY,
                'label'       => 'settings_checkout_string_saturday_delivery',
                'description' => 'settings_checkout_string_standard_delivery_description',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_SIGNATURE,
                'label' => 'settings_checkout_string_signature',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_ONLY_RECIPIENT,
                'label' => 'settings_checkout_string_only_recipient',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_PICKUP,
                'label' => 'settings_checkout_string_pickup',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_HOUSE_NUMBER,
                'label' => 'settings_checkout_string_house_number',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_CITY,
                'label' => 'settings_checkout_string_city',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_POSTAL_CODE,
                'label' => 'settings_checkout_string_postal_code',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_COUNTRY,
                'label' => 'settings_checkout_string_country',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_OPENING_HOURS,
                'label' => 'settings_checkout_string_opening_hours',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_LOAD_MORE,
                'label' => 'settings_checkout_string_load_more',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_PICKUP_LOCATIONS_MAP_BUTTON,
                'label' => 'settings_checkout_string_pickup_locations_map_button',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_PICKUP_LOCATIONS_LIST_BUTTON,
                'label' => 'settings_checkout_string_pickup_locations_list_button',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_RETRY,
                'label' => 'settings_checkout_string_retry',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_ADDRESS_NOT_FOUND,
                'label' => 'settings_checkout_string_address_not_found',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_WRONG_POSTAL_CODE_CITY,
                'label' => 'settings_checkout_string_wrong_postal_code_city',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_WRONG_NUMBER_POSTAL_CODE,
                'label' => 'settings_checkout_string_wrong_number_postal_code',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_FROM,
                'label' => 'settings_checkout_string_from',
            ],
            [
                'class' => TextInput::class,
                'name'  => CheckoutSettings::STRING_DISCOUNT,
                'label' => 'settings_checkout_string_discount',
            ],
        ]);
    }
}

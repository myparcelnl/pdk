<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Settings\Model\DeliveryOptionsStringsSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class DeliveryOptionsStringsSettingsView extends AbstractView
{
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function getFields(): Collection
    {
        return new Collection([
            [
                'class'       => TextInput::class,
                'name'        => DeliveryOptionsStringsSettings::DELIVERY_TITLE,
                'label'       => 'Delivery Title',
                'description' => 'Title of the delivery option.',
            ],
            [
                'class'       => TextInput::class,
                'name'        => DeliveryOptionsStringsSettings::STANDARD_DELIVERY_TITLE,
                'label'       => 'Standard delivery title',
                'description' => 'When there is no title, the delivery time will automatically be visible',
            ],
            [
                'class'       => TextInput::class,
                'name'        => DeliveryOptionsStringsSettings::MORNING_DELIVERY_TITLE,
                'label'       => 'Morning delivery title',
                'description' => 'When there is no title, the delivery time will automatically be visible',
            ],
            [
                'class'       => TextInput::class,
                'name'        => DeliveryOptionsStringsSettings::EVENING_DELIVERY_TITLE,
                'label'       => 'Evening delivery title',
                'description' => 'When there is no title, the delivery time will automatically be visible',
            ],
            [
                'class'       => TextInput::class,
                'name'        => DeliveryOptionsStringsSettings::SATURDAY_DELIVERY_TITLE,
                'label'       => 'Saturday delivery title',
                'description' => 'When there is no title, the delivery time will automatically be visible',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::SIGNATURE_TITLE,
                'label' => 'Signature title',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::ONLY_RECIPIENT_TITLE,
                'label' => 'Only recipient title',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::PICKUP_TITLE,
                'label' => 'Pickup title',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::HOUSE_NUMBER,
                'label' => 'House number text',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::CITY,
                'label' => 'City text',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::POSTCODE,
                'label' => 'Postal code text',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::CC,
                'label' => 'Country text',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::OPENING_HOURS,
                'label' => 'Opening hours text',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::LOAD_MORE,
                'label' => 'Load more title',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::PICKUP_LOCATIONS_MAP_BUTTON,
                'label' => 'Pickup map button title',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::PICKUP_LOCATIONS_LIST_BUTTON,
                'label' => 'Pickup list button text',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::RETRY,
                'label' => 'Retry title',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::ADDRESS_NOT_FOUND,
                'label' => 'Address not found title',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::WRONG_POSTAL_CODE_CITY,
                'label' => 'Wrong postal code/city combination title',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::WRONG_NUMBER_POSTAL_CODE,
                'label' => 'Wrong number/postal code title',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::FROM,
                'label' => 'From title',
            ],
            [
                'class' => TextInput::class,
                'name'  => DeliveryOptionsStringsSettings::DISCOUNT,
                'label' => 'Discount title',
            ],
        ]);
    }
}

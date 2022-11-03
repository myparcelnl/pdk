<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\Input\CheckboxInput;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;
use MyParcelNL\Pdk\Form\Model\Input\ToggleInput;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;

/**
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class OrderSettingsView extends AbstractView
{
    // todo: incorporate plugins' order statuses
    /**
     * @return \MyParcelNL\Pdk\Base\Support\Collection
     */
    protected function getFields(): Collection
    {
        return new Collection([
            [
                'class'   => SelectInput::class,
                'name'    => OrderSettings::STATUS_ON_LABEL_CREATE,
                'label'   => 'settings_order_status_on_label_create',
                'options' => [],
            ],
            [
                'class'   => SelectInput::class,
                'name'    => OrderSettings::STATUS_WHEN_LABEL_SCANNED,
                'label'   => 'settings_order_status_when_label_scanned',
                'options' => [],
            ],
            [
                'class'   => SelectInput::class,
                'name'    => OrderSettings::STATUS_WHEN_DELIVERED,
                'label'   => 'settings_order_status_when_delivered',
                'options' => [],
            ],
            [
                'class' => CheckboxInput::class,
                'name'  => OrderSettings::IGNORE_ORDER_STATUSES,
                'label' => 'settings_order_ignore_order_statuses',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => OrderSettings::ORDER_STATUS_MAIL,
                'label' => 'settings_order_order_status_mail',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => OrderSettings::SEND_NOTIFICATION_AFTER,
                'label'   => 'settings_order_send_notification_after',
                'options' => [],
            ],
            [
                'class' => ToggleInput::class,
                'name'  => OrderSettings::SEND_ORDER_STATE_FOR_DIGITAL_STAMP,
                'label' => 'settings_order_send_order_state_for_digital_stamp',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => OrderSettings::SAVE_CUSTOMER_ADDRESS,
                'label' => 'settings_order_save_customer_address',
            ],
            [
                'class' => TextInput::class,
                'name'  => OrderSettings::EMPTY_PARCEL_WEIGHT,
                'label' => 'settings_order_save_customer_address',
            ],
            [
                'class' => TextInput::class,
                'name'  => OrderSettings::SAVE_CUSTOMER_ADDRESS,
                'label' => 'settings_order_save_customer_address',
            ],
        ]);
    }
}

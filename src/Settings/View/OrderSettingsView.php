<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\View;

use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Form\Model\Input\CheckboxInput;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;
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
                'label'   => 'Order status when label created',
                'options' => [],
            ],
            [
                'class'   => SelectInput::class,
                'name'    => OrderSettings::STATUS_WHEN_LABEL_SCANNED,
                'label'   => 'Order status when label scanned',
                'options' => [],
            ],
            [
                'class'   => SelectInput::class,
                'name'    => OrderSettings::STATUS_WHEN_DELIVERED,
                'label'   => 'Order status when delivered',
                'options' => [],
            ],
            [
                'class' => CheckboxInput::class,
                'name'  => OrderSettings::IGNORE_ORDER_STATUSES,
                'label' => 'Ignore order statuses',
            ],
            [
                'class' => ToggleInput::class,
                'name'  => OrderSettings::ORDER_STATUS_MAIL,
                'label' => 'Order status mail',
            ],
            [
                'class'   => SelectInput::class,
                'name'    => OrderSettings::SEND_NOTIFICATION_AFTER,
                'label'   => 'Send notification after',
                'options' => [],
            ],
            [
                'class' => ToggleInput::class,
                'name'  => OrderSettings::SEND_ORDER_STATE_FOR_DIGITAL_STAMPS,
                'label' => 'Automatic set order state to sent for digital stamp',
            ],
        ]);
    }
}

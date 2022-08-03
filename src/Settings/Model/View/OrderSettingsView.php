<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model\View;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Form\Model\Input\SelectInput;

/**
 * @property \MyParcelNL\Pdk\Form\Model\Input\SelectInput $statusOnLabelCreate
 * @property \MyParcelNL\Pdk\Form\Model\Input\SelectInput $statusWhenLabelScanned
 * @property \MyParcelNL\Pdk\Form\Model\Input\SelectInput $statusWhenDelivered
 */
class OrderSettingsView extends Model
{
    // todo: import statuslist from plugin platform inside the options
    // - ignore order statuses
    // - order status mail
    // - send notification after

    public function __construct(array $data = null)
    {
        $this->attributes['statusOnLabelCreate']    = [
            'type'    => 'select',
            'name'    => 'statusOnLabelCreate',
            'label'   => 'Order status when label created',
            'options' => [],
        ];
        $this->attributes['statusWhenLabelScanned'] = [
            'type'    => 'select',
            'name'    => 'statusWhenLabelScanned',
            'label'   => 'Order status when label scanned',
            'options' => [],
        ];
        $this->attributes['statusWhenDelivered']    = [
            'type'    => 'select',
            'name'    => 'statusWhenDelivered',
            'label'   => 'Order status when delivered',
            'options' => [],
        ];

        $this->casts['statusOnLabelCreate']    = SelectInput::class;
        $this->casts['statusWhenLabelScanned'] = SelectInput::class;
        $this->casts['statusWhenDelivered']    = SelectInput::class;

        parent::__construct($data);
    }
}

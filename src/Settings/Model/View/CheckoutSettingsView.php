<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model\View;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;

/**
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput $deliveryTitle
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput $standardDeliveryTitle
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput $morningDeliveryTitle
 */
class CheckoutSettingsView extends Model
{
    public function __construct(array $data = null)
    {
        $this->attributes['deliveryTitle']         = [
            'type'  => 'text',
            'name'  => 'deliveryTitle',
            'label' => 'Delivery Title',
            'desc'  => 'Title of the delivery option.',
        ];
        $this->attributes['standardDeliveryTitle'] = [
            'type'  => 'text',
            'name'  => 'standardDeliveryTitle',
            'label' => 'Standard delivery title',
            'desc'  => 'When there is no title, the delivery time will automatically be visible',
        ];
        $this->attributes['morningDeliveryTitle']  = [
            'type'  => 'text',
            'name'  => 'morningDeliveryTitle',
            'label' => 'Morning delivery title',
            'desc'  => 'When there is no title, delivery time will automatically be visible',
        ];

        $this->casts['deliveryTitle']         = TextInput::class;
        $this->casts['standardDeliveryTitle'] = TextInput::class;
        $this->casts['morningDeliveryTitle']  = TextInput::class;

        parent::__construct($data);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model\View;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Form\Model\Input\TextInput;

/**
 * @property \MyParcelNL\Pdk\Form\Model\Input\TextInput $deliveryTitle
 */
class CheckoutSettingsView extends Model
{
    public function __construct(array $data = null)
    {
        $this->attributes['deliveryTitle'] = [
            'type'  => 'text',
            'name'  => 'deliveryTitle',
            'label' => 'Delivery Title',
            'desc'  => 'Title of the delivery option.',
        ];

        $this->casts['deliveryTitle'] = TextInput::class;

        parent::__construct($data);
    }
}

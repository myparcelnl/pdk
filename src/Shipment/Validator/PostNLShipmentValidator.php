<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Validator;

use MyParcelNL\Pdk\Shipment\Rule\DeliveryTypeRule;
use MyParcelNL\Sdk\src\Validator\AbstractValidator;

class PostNLShipmentValidator extends AbstractValidator
{
    /**
     * @return \MyParcelNL\Sdk\src\Rule\Rule[]
     */
    protected function getRules(): array
    {
        return [
            new DeliveryTypeRule(),
        ];
    }
}

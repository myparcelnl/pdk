<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Validator;

use MyParcelNL\Sdk\src\Validator\AbstractValidator;

class BpostShipmentValidator extends AbstractValidator
{
    /**
     * @return \MyParcelNL\Sdk\src\Rule\Rule[]
     */
    protected function getRules(): array
    {
        return [];
    }
}

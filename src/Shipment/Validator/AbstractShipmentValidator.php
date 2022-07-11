<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Validator;

use MyParcelNL\Sdk\src\Validator\AbstractValidator;

abstract class AbstractShipmentValidator extends AbstractValidator
{
    /**
     * @return string
     */
    abstract protected function getCarrierClass(): string;
}

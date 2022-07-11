<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Factory;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Sdk\src\Model\Carrier\CarrierFactory;
use MyParcelNL\Sdk\src\Validator\AbstractValidator;

class ShipmentValidatorFactory
{
    /**
     * @param  string|int|\MyParcelNL\Sdk\src\Model\Carrier\AbstractCarrier $carrier
     *
     * @return \MyParcelNL\Sdk\src\Validator\AbstractValidator
     * @throws \Exception
     */
    public static function create($carrier): AbstractValidator
    {
        $carrierInstance = CarrierFactory::create($carrier);
        $name            = $carrierInstance->getName();
        $validator       = Config::get("carriers.$name.validator");

        return new $validator();
    }
}

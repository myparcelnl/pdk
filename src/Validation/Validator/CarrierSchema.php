<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;

class CarrierSchema extends OrderPropertiesValidator
{
    /**
     * @var \MyParcelNL\Pdk\Carrier\Model\CarrierOptions
     */
    protected $carrierOptions;

    /**
     * @return array
     */
    public function getSchema(): array
    {
        return $this->carrierOptions->capabilities->toArray();
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\CarrierOptions $carrier
     *
     * @return self
     * @noinspection PhpUnused
     */
    public function setCarrierOptions(CarrierOptions $carrier): self
    {
        $this->carrierOptions = $carrier;
        return $this;
    }
}

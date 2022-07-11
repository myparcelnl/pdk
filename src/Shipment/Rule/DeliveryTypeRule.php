<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Rule;

use MyParcelNL\Pdk\Base\Config;
use MyParcelNL\Sdk\src\Rule\Rule;

class DeliveryTypeRule extends Rule
{
    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $validationSubject
     *
     * @return void
     */
    public function validate($validationSubject): void
    {
        $deliveryOptions = $validationSubject->getDeliveryOptions();
        $allowed         = Config::get("carriers.{$validationSubject->carrier->getName()}.delivery_types");

        $deliveryType = $deliveryOptions->getDeliveryType();

        if (! $deliveryOptions->getDeliveryType() || in_array($deliveryType, $allowed, true)) {
            return;
        }

        $this->addError(
            sprintf('Delivery type %s is not allowed for %s', $deliveryType, $validationSubject->carrier->getHuman())
        );
    }
}

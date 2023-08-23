<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Validation\Validator;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;

final class DynamicOrderValidator extends OrderValidator
{
    /**
     * @param  string $cc
     *
     * @return $this
     */
    public function inCountry(string $cc): self
    {
        $this->order->shippingAddress->cc = $cc;

        return $this;
    }

    /**
     * Clone the order to prevent side effects.
     *
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\Validation\Validator\OrderValidatorInterface
     */
    public function setOrder(PdkOrder $order): OrderValidatorInterface
    {
        return parent::setOrder(clone $order);
    }

    /**
     * @param  string $carrier
     *
     * @return $this
     */
    public function withCarrier(string $carrier): self
    {
        $this->order->deliveryOptions->carrier = $carrier;

        return $this;
    }

    /**
     * @param  string $packageType
     *
     * @return $this
     */
    public function withPackageType(string $packageType): self
    {
        $this->order->deliveryOptions->packageType = $packageType;

        return $this;
    }
}

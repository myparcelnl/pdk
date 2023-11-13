<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Validation\Validator\CarrierSchema;

final class CustomerInformationCalculator extends AbstractPdkOrderOptionCalculator
{
    public function calculate(): void
    {
        $orderCarrier = $this->order->deliveryOptions->carrier;

        if (! $this->sharingCustomerInformation($orderCarrier)) {
            $this->order->shippingAddress->email = null;
            $this->order->shippingAddress->phone = null;

            if ($this->order->billingAddress) {
                $this->order->billingAddress->email = null;
                $this->order->billingAddress->phone = null;
            }
        }

        $this->order->shipments
            ->filter(function (Shipment $shipment) {
                return isset($shipment->recipient)
                    && ! $this->sharingCustomerInformation($shipment->deliveryOptions->carrier);
            })
            ->each(function (Shipment $shipment) {
                $shipment->recipient->email = null;
                $shipment->recipient->phone = null;
            });
    }

    /**
     * @param  \MyParcelNL\Pdk\Carrier\Model\Carrier $carrier
     *
     * @return bool
     */
    protected function sharingCustomerInformation(Carrier $carrier): bool
    {
        /** @var \MyParcelNL\Pdk\Validation\Validator\CarrierSchema $schema */
        $schema = Pdk::get(CarrierSchema::class);

        $schema->setCarrier($carrier);

        $carrierNeedsCustomerInfo = $schema->needsCustomerInfo();
        $sharingCustomerInfo      = Settings::get(OrderSettings::SHARE_CUSTOMER_INFORMATION, OrderSettings::ID);

        return $carrierNeedsCustomerInfo || $sharingCustomerInfo;
    }
}

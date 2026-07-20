<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Calculator\General;

use MyParcelNL\Pdk\App\Order\Calculator\AbstractPdkOrderOptionCalculator;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Sdk\Client\Generated\CoreApi\Model\RefTypesCarrierV2;

final class CustomerInformationCalculator extends AbstractPdkOrderOptionCalculator
{
    /*
     * These carriers *requires* customer information to be shared, ignore any global setting.
     * There is no API exposing this type of requirement at this point in time (july 2026)
     */
    private const CARRIERS_WITH_MANDATORY_CUSTOMER_INFO = [
        RefTypesCarrierV2::DPD,
        RefTypesCarrierV2::TRUNKRS
    ];

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
        // @TODO this is a specific carrier check as there is currently no endpoint exposing this information
        if (\in_array($carrier->carrier, self::CARRIERS_WITH_MANDATORY_CUSTOMER_INFO, true)) {
            return true;
        }

        return !!Settings::get(OrderSettings::SHARE_CUSTOMER_INFORMATION, OrderSettings::ID);
    }
}

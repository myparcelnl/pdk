<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Concern;

use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\ShipmentOptions;

trait DecodesDeliveryOptions
{
    use DecodesAddresses;

    /**
     * @param  array $data
     *
     * @return array
     */
    private function decodeCarrier(array $data): array
    {
        return [
            'id'             => Arr::get($data, 'carrier_id'),
            'subscriptionId' => Arr::get($data, 'contract_id'),
        ];
    }

    /**
     * @param  array $shipment
     *
     * @return array
     */
    private function decodeDeliveryOptions(array $shipment): array
    {
        return [
            DeliveryOptions::CARRIER          => $this->decodeCarrier($shipment),
            DeliveryOptions::DATE             => Arr::get($shipment, 'options.delivery_date'),
            DeliveryOptions::DELIVERY_TYPE    => Arr::get($shipment, 'delivery_type'),
            DeliveryOptions::PACKAGE_TYPE     => Arr::get($shipment, 'package_type'),
            DeliveryOptions::SHIPMENT_OPTIONS => $this->decodeShipmentOptions($shipment),
            DeliveryOptions::PICKUP_LOCATION  => $this->decodeRetailLocation(Arr::get($shipment, 'pickup') ?? []),
        ];
    }

    /**
     * @param  array $options
     *
     * @return array
     */
    private function decodeShipmentOptions(array $options): array
    {
        $keys            = array_keys((new ShipmentOptions())->getAttributes(Arrayable::CASE_SNAKE));
        $shipmentOptions = Arr::only($options, $keys);

        $shipmentOptions['insurance'] = Arr::get($options, 'insurance.amount');

        return $shipmentOptions;
    }
}

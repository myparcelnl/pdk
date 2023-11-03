<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Helpers;

use InvalidArgumentException;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

final class CarrierSnapshotHelper
{
    /**
     * @param  Collection|\MyParcelNL\Pdk\Base\Model\Model $input
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public static function removeCapabilities($input): array
    {
        if ($input instanceof PdkOrder) {
            return Utils::filterNull(self::removeFromOrder($input));
        }

        if ($input instanceof DeliveryOptions) {
            return Utils::filterNull(self::removeFromDeliveryOptions($input));
        }

        if ($input instanceof ShipmentCollection) {
            return Utils::filterNull(self::removeFromShipments($input));
        }

        if ($input instanceof Shipment) {
            return Utils::filterNull(self::removeFromShipment($input));
        }

        if ($input instanceof Carrier) {
            return $input->toStorableArray();
        }

        throw new InvalidArgumentException('Invalid input type');
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $model
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private static function removeFromDeliveryOptions(DeliveryOptions $model): array
    {
        return array_replace(
            $model->except(['carrier']),
            $model->only(['carrier'], Arrayable::STORABLE_NULL)
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private static function removeFromOrder(PdkOrder $order): array
    {
        return array_replace(
            $order->except(['deliveryOptions', 'shipments'], Arrayable::SKIP_NULL),
            [
                'deliveryOptions' => self::removeFromDeliveryOptions($order->deliveryOptions),
                'shipments'       => self::removeFromShipments($order->shipments),
            ]
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private static function removeFromShipment(Shipment $shipment): array
    {
        return array_replace(
            $shipment->except(['carrier', 'deliveryOptions'], Arrayable::SKIP_NULL),
            $shipment->only(['carrier'], Arrayable::STORABLE_NULL),
            ['deliveryOptions' => self::removeFromDeliveryOptions($shipment->deliveryOptions)]
        );
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     *
     * @return array
     */
    private static function removeFromShipments(ShipmentCollection $shipments): array
    {
        return (new Collection($shipments))
            ->map(static function (Shipment $shipment) {
                return self::removeFromShipment($shipment);
            })
            ->toArrayWithoutNull();
    }
}

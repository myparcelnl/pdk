<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

/**
 * @property string                                                 $externalIdentifier
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration $customsDeclaration
 * @property \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions         $deliveryOptions
 * @property \MyParcelNL\Pdk\Base\Model\ContactDetails              $recipient
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails         $sender
 * @property \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
 */
class OrderDataContext extends PdkOrder
{
    public const ID = Context::ID_ORDER_DATA;

    /**
     * Remove deleted shipments from the array.
     *
     * @param  null|int $flags
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toArray(?int $flags = null): array
    {
        $array = parent::toArray($flags);

        return array_replace($array, [
            'shipments' => array_values(
                Arr::where($array['shipments'], static function (array $shipment) {
                    return ! $shipment['deleted'];
                })
            ),
        ]);
    }
}

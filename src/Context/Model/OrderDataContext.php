<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\Context\Context;

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
        if ($this->cloned) {
            return parent::toArray($flags);
        }

        $clone = clone $this;

        $clone->shipments = $clone->shipments
            ->filterNotDeleted()
            ->values();

        return $clone->toArray($flags);
    }
}

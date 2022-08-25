<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Plugin\Context;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;

/**
 * @property null|string                                                 $externalIdentifier
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration      $customsDeclaration
 * @property null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions         $deliveryOptions
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $recipient
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $sender
 * @property null|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
 * @property null|\MyParcelNL\Pdk\Shipment\Model\Label                   $label
 */
class OrderDataContext extends PdkOrder
{
    public const ID = Context::ID_ORDER_DATA;
}

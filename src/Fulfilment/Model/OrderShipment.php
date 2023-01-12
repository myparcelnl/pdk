<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Fulfilment\Collection\ShippedItemCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

/**
 * @property  string                                                      $uuid
 * @property  string                                                      $externalShipmentIdentifier
 * @property  int                                                         $shipmentId
 * @property  \MyParcelNL\Pdk\Shipment\Model\Shipment                     $shipment
 * @property  \MyParcelNL\Pdk\Fulfilment\Collection\ShippedItemCollection $shippedItems
 */
class OrderShipment extends Model
{
    public    $attributes = [
        'uuid'                       => null,
        'externalShipmentIdentifier' => null,
        'shipmentId'                 => null,
        'shipment'                   => Shipment::class,
        'shippedItems'               => ShippedItemCollection::class,
    ];

    protected $casts      = [
        'uuid'                       => 'string',
        'externalShipmentIdentifier' => 'string',
        'shipmentId'                 => 'int',
        'shipment'                   => Shipment::class,
        'shippedItems'               => ShippedItemCollection::class,
    ];
}

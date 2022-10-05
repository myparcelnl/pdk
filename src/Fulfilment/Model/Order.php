<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderLineCollection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;

/**
 * Order for use with fulfilment API.
 *
 * @property null|int                                                       $accountId
 * @property null|string                                                    $createdAt
 * @property null|string                                                    $externalIdentifier
 * @property null|string                                                    $fulfilmentPartnerIdentifier
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails                 $invoiceAddress
 * @property null|string                                                    $language
 * @property null|string                                                    $orderDate
 * @property null|\MyParcelNL\Pdk\Fulfilment\Collection\OrderLineCollection $orderLines
 * @property null|int                                                       $price
 * @property null|int                                                       $priceAfterVat
 * @property null|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection    $shipments
 * @property null|int                                                       $shopId
 * @property null|string                                                    $status
 * @property null|string                                                    $type
 * @property null|string                                                    $updatedAt
 * @property null|string                                                    $uuid
 * @property null|int                                                       $vat
 */
class Order extends Model
{
    protected $attributes = [
        'accountId'                   => null,
        'createdAt'                   => null,
        'externalIdentifier'          => null,
        'fulfilmentPartnerIdentifier' => null,
        'invoiceAddress'              => null,
        'language'                    => null,
        'orderDate'                   => null,
        'orderLines'                  => null,
        'price'                       => null,
        'priceAfterVat'               => null,
        'shipments'                   => ShipmentCollection::class,
        'shopId'                      => null,
        'status'                      => null,
        'type'                        => null,
        'updatedAt'                   => null,
        'uuid'                        => null,
        'vat'                         => null,
    ];

    protected $casts      = [
        'accountId'                   => 'int',
        'createdAt'                   => 'string',
        'externalIdentifier'          => 'string',
        'fulfilmentPartnerIdentifier' => 'string',
        'invoiceAddress'              => ContactDetails::class,
        'language'                    => 'string',
        'orderDate'                   => 'string',
        'orderLines'                  => OrderLineCollection::class,
        'price'                       => 'int',
        'priceAfterVat'               => 'int',
        'shipments'                   => ShipmentCollection::class,
        'shopId'                      => 'int',
        'status'                      => 'string',
        'type'                        => 'string',
        'updatedAt'                   => 'string',
        'uuid'                        => 'string',
        'vat'                         => 'int',
    ];
}

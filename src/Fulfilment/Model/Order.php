<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\Model;

/**
 * Order for use with fulfilment API.
 *
 * @property null|string $uuid
 * @property null|int    $accountId
 * @property null|string $createdAt
 * @property null|int    $shopId
 * @property null|string $expectedDeliveryDate
 * @property null|string $expectedDeliveryTimeframe
 * @property null|string $externalIdentifier
 * @property null|string $fulfilmentPartnerIdentifier
 * @property null|array  $invoiceAddress
 * @property null|string $language
 * @property null|string $orderDate
 * @property null|array  $orderLines
 * @property null|int    $price
 * @property null|int    $priceAfterVat
 * @property null|array  $shipment
 * @property null|string $status
 * @property null|string $type
 * @property null|string $updatedAt
 * @property null|int    $vat
 */
class Order extends Model
{
    protected $attributes = [
        'accountId'                   => null,
        'createdAt'                   => null,
        'expectedDeliveryDate'        => null,
        'expectedDeliveryTimeframe'   => null,
        'externalIdentifier'          => null,
        'fulfilmentPartnerIdentifier' => null,
        'invoiceAddress'              => null,
        'language'                    => null,
        'orderDate'                   => null,
        'orderLines'                  => null,
        'price'                       => null,
        'priceAfterVat'               => null,
        'shipment'                    => null,
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
        'expectedDeliveryDate'        => 'string',
        'expectedDeliveryTimeframe'   => 'string',
        'externalIdentifier'          => 'string',
        'fulfilmentPartnerIdentifier' => 'string',
        'invoiceAddress'              => 'array',
        'language'                    => 'string',
        'orderDate'                   => 'string',
        'orderLines'                  => 'array',
        'price'                       => 'int',
        'priceAfterVat'               => 'int',
        'shipment'                    => 'array',
        'shopId'                      => 'int',
        'status'                      => 'string',
        'type'                        => 'string',
        'updatedAt'                   => 'string',
        'uuid'                        => 'string',
        'vat'                         => 'int',
    ];
}

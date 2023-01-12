<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderLineCollection;
use MyParcelNL\Pdk\Plugin\Model\PdkOrder;
use MyParcelNL\Pdk\Plugin\Model\PdkOrderLine;

/**
 * Order for use with fulfilment API.
 *
 * @property null|string                                               $uuid
 * @property null|string                                               $externalIdentifier
 * @property null|string                                               $fulfilmentPartnerIdentifier
 * @property null|int                                                  $shopId
 * @property null|int                                                  $accountId
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails            $invoiceAddress
 * @property null|string                                               $language
 * @property null|string                                               $orderDate
 * @property \MyParcelNL\Pdk\Fulfilment\Collection\OrderLineCollection $orderLines
 * @property null|\MyParcelNL\Pdk\Fulfilment\Model\Shipment            $shipment
 * @property null|string                                               $status
 * @property null|string                                               $type
 * @property int                                                       $price
 * @property int                                                       $vat
 * @property int                                                       $priceAfterVat
 * @property null|string                                               $createdAt
 * @property null|string                                               $updatedAt
 */
class Order extends Model
{
    protected $attributes = [
        'uuid'                        => null,
        'externalIdentifier'          => null,
        'fulfilmentPartnerIdentifier' => null,
        'shopId'                      => null,
        'accountId'                   => null,
        'invoiceAddress'              => null,
        'language'                    => null,
        'orderDate'                   => null,
        'orderLines'                  => OrderLineCollection::class,
        'shipment'                    => null,
        'status'                      => null,
        'type'                        => null,
        'price'                       => 0,
        'vat'                         => 0,
        'priceAfterVat'               => 0,
        'createdAt'                   => null,
        'updatedAt'                   => null,
    ];

    protected $casts      = [
        'uuid'                        => 'string',
        'externalIdentifier'          => 'string',
        'fulfilmentPartnerIdentifier' => 'string',
        'shopId'                      => 'int',
        'accountId'                   => 'int',
        'invoiceAddress'              => ContactDetails::class,
        'language'                    => 'string',
        'orderDate'                   => 'datetime',
        'orderLines'                  => OrderLineCollection::class,
        'shipment'                    => Shipment::class,
        'status'                      => 'string',
        'type'                        => 'string',
        'price'                       => 'int',
        'vat'                         => 'int',
        'priceAfterVat'               => 'int',
        'createdAt'                   => 'datetime',
        'updatedAt'                   => 'datetime',
    ];

    /**
     * @param  null|\MyParcelNL\Pdk\Plugin\Model\PdkOrder $pdkOrder
     *
     * @return static
     * @noinspection PhpUnused
     */
    public static function fromPdkOrder(?PdkOrder $pdkOrder): self
    {
        if (! $pdkOrder) {
            return new self();
        }

        $shipment = $pdkOrder->shipments->isNotEmpty()
            ? Shipment::fromPdkShipment($pdkOrder->shipments->first())
            : [
                'deliveryOptions' => $pdkOrder->deliveryOptions,
                'recipient'       => $pdkOrder->recipient,
            ];

        return new static(
            [
                'externalIdentifier'          => $pdkOrder->externalIdentifier,
                'fulfilmentPartnerIdentifier' => null,
                'deliveryOptions'             => $pdkOrder->deliveryOptions,
                // TODO: add billing address to pdk order
                'invoiceAddress'              => null,
                'language'                    => LanguageService::getLanguage(),
                'orderDate'                   => $pdkOrder->orderDate,
                'orderLines'                  => $pdkOrder->lines
                    ->map(function (PdkOrderLine $pdkOrderLine) {
                        return new OrderLine(
                            [
                                'product' => Product::fromPdkProduct($pdkOrderLine->product),
                            ] + $pdkOrderLine->toSnakeCaseArray()
                        );
                    })
                    ->toArray(),
                'shipment'                    => $shipment,
                'price'                       => $pdkOrder->orderPrice,
                'priceAfterVat'               => $pdkOrder->orderPriceAfterVat,
                'vat'                         => $pdkOrder->orderVat,
                'status'                      => null,
                'type'                        => null,
            ]
        );
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderLine;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderLineCollection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;

/**
 * Order for use with fulfilment API.
 *
 * @property null|string                                               $uuid
 * @property null|string                                               $externalIdentifier
 * @property null|string                                               $fulfilmentPartnerIdentifier
 * @property null|int                                                  $shopId
 * @property null|int                                                  $accountId
 * @property null|\MyParcelNL\Pdk\Shipment\Model\RetailLocation        $dropOffPoint
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails            $invoiceAddress
 * @property null|string                                               $language
 * @property null|\DateTime                                            $orderDate
 * @property \MyParcelNL\Pdk\Fulfilment\Collection\OrderLineCollection $lines
 * @property \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection $notes
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
        'dropOffPoint'                => null,
        'invoiceAddress'              => null,
        'language'                    => null,
        'orderDate'                   => null,
        'lines'                       => OrderLineCollection::class,
        'notes'                       => OrderNoteCollection::class,
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
        'dropOffPoint'                => RetailLocation::class,
        'invoiceAddress'              => ContactDetails::class,
        'language'                    => 'string',
        'orderDate'                   => 'datetime',
        'lines'                       => OrderLineCollection::class,
        'notes'                       => OrderNoteCollection::class,
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
     * @param  null|\MyParcelNL\Pdk\App\Order\Model\PdkOrder $pdkOrder
     *
     * @return static
     * @noinspection PhpUnused
     * @throws \Exception
     */
    public static function fromPdkOrder(?PdkOrder $pdkOrder): self
    {
        if (! $pdkOrder) {
            return new static();
        }

        $shipment = Shipment::fromPdkShipment($pdkOrder->createShipment());

        return new static(
            [
                'externalIdentifier'          => $pdkOrder->externalIdentifier,
                'fulfilmentPartnerIdentifier' => null,
                'deliveryOptions'             => $pdkOrder->deliveryOptions,
                'invoiceAddress'              => $pdkOrder->billingAddress ?? null,
                'language'                    => Language::getIso2(),
                'orderDate'                   => $pdkOrder->orderDate,
                'lines'                       => $pdkOrder->lines
                    ->map(fn(PdkOrderLine $pdkOrderLine) => new OrderLine(
                        [
                            'product' => Product::fromPdkProduct($pdkOrderLine->product),
                        ] + $pdkOrderLine->toSnakeCaseArray()
                    ))
                    ->all(),
                'notes'                       => $pdkOrder->notes,
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

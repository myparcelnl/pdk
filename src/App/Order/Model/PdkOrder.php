<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\PhysicalProperties;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Validation\Validator\OrderValidator;

/**
 * @property null|string                                                 $externalIdentifier
 * @property null|string                                                 $apiIdentifier
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration      $customsDeclaration
 * @property \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions              $deliveryOptions
 * @property \MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection $lines
 * @property \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection $notes
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $senderAddress
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $billingAddress
 * @property \MyParcelNL\Pdk\App\Order\Model\ShippingAddress             $shippingAddress
 * @property null|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
 * @property null|\DateTimeImmutable                                     $orderDate
 * @property bool                                                        $exported
 * @property \MyParcelNL\Pdk\Shipment\Model\PhysicalProperties           $physicalProperties
 * @property int                                                         $shipmentPrice
 * @property int                                                         $shipmentPriceAfterVat
 * @property int                                                         $shipmentVat
 * @property int                                                         $orderPrice
 * @property int                                                         $orderPriceAfterVat
 * @property int                                                         $orderVat
 * @property int                                                         $totalPrice
 * @property int                                                         $totalPriceAfterVat
 * @property int                                                         $totalVat
 */
class PdkOrder extends Model implements StorableArrayable
{
    protected $attributes = [
        /** Plugin order id */
        'externalIdentifier' => null,

        /** Fulfilment order ID from MyParcel */
        'apiIdentifier'      => null,

        'deliveryOptions' => DeliveryOptions::class,

        'senderAddress'      => null,
        'billingAddress'     => null,
        'shippingAddress'    => ShippingAddress::class,

        /**
         * Order shipments. Applicable when NOT using order mode.
         */
        'shipments'          => ShipmentCollection::class,
        'customsDeclaration' => null,
        'physicalProperties' => PhysicalProperties::class,
        'lines'              => PdkOrderLineCollection::class,
        'notes'              => null,

        /**
         * Timestamp of when the order was placed.
         */
        'orderDate'          => null,

        /**
         * Whether the order has been exported as an entire order. Applicable only when using order mode.
         */
        'exported'           => false,

        'shipmentPrice'         => 0,
        'shipmentPriceAfterVat' => 0,
        'shipmentVat'           => 0,

        /* The following values are calculated automatically */
        'orderPrice'            => 0,
        'orderPriceAfterVat'    => 0,
        'orderVat'              => 0,
        'totalPrice'            => 0,
        'totalVat'              => 0,
        'totalPriceAfterVat'    => 0,
    ];

    protected $casts      = [
        'externalIdentifier' => 'string',
        'apiIdentifier'      => 'string',

        'deliveryOptions' => DeliveryOptions::class,

        'billingAddress'  => ContactDetails::class,
        'shippingAddress' => ShippingAddress::class,
        'senderAddress'   => ContactDetails::class,

        'shipments'          => ShipmentCollection::class,
        'customsDeclaration' => CustomsDeclaration::class,
        'physicalProperties' => PhysicalProperties::class,
        'lines'              => PdkOrderLineCollection::class,
        'notes'              => PdkOrderNoteCollection::class,

        'orderDate'             => 'datetime',
        'exported'              => 'bool',
        'shipmentPrice'         => 'int',
        'shipmentPriceAfterVat' => 'int',
        'shipmentVat'           => 'int',
        'orderPrice'            => 'int',
        'orderPriceAfterVat'    => 'int',
        'orderVat'              => 'int',
        'totalPrice'            => 'int',
        'totalVat'              => 'int',
        'totalPriceAfterVat'    => 'int',
    ];

    // TODO: v3.0.0 stop supporting deprecated attributes
    protected $deprecated = [
        'orderLines' => 'lines',
        'orderNotes' => 'notes',
        'recipient'  => 'shippingAddress',
        'sender'     => 'senderAddress',
    ];

    /**
     * @var \MyParcelNL\Pdk\Validation\Validator\OrderValidator
     */
    private $validator;

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        $this->updateShipments();
        $this->updateTotals();
    }

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\Order $order
     *
     * @return self
     */
    public static function fromFulfilmentOrder(Order $order): self
    {
        return new self([
            'externalIdentifier' => $order->externalIdentifier,
            'apiIdentifier'      => $order->uuid,
            'orderDate'          => $order->orderDate,
            'invoiceAddress'     => $order->invoiceAddress,
            'dropOffPoint'       => $order->dropOffPoint,
            'notes'              => new PdkOrderNoteCollection($order->notes->all()),
            'lines'              => new PdkOrderLineCollection($order->lines->all()),
            'status'             => $order->status,
            'type'               => $order->type,
            'price'              => $order->price,
            'vat'                => $order->vat,
            'priceAfterVat'      => $order->priceAfterVat,
            'createdAt'          => $order->createdAt,
            'updatedAt'          => $order->updatedAt,
        ]);
    }

    /**
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     * @throws \Exception
     */
    public function createShipment(): Shipment
    {
        $deliveryOptions = $this->deliveryOptions;

        return new Shipment([
            'customsDeclaration'  => $this->customsDeclaration,
            'deliveryOptions'     => $deliveryOptions,
            'recipient'           => $this->shippingAddress,
            'sender'              => $this->senderAddress,
            'referenceIdentifier' => $this->externalIdentifier,
            'carrier'             => $deliveryOptions->carrier,
            'orderId'             => $this->externalIdentifier,
            'dropOffPoint'        => null,
            'physicalProperties'  => $this->physicalProperties,
        ]);
    }

    /**
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     * @noinspection PhpUnused
     */
    public function getNotesAttribute(): PdkOrderNoteCollection
    {
        if (isset($this->attributes['notes'])) {
            return $this->getCastAttribute('notes');
        }

        /** @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface $orderNoteRepository */
        $orderNoteRepository = Pdk::get(PdkOrderNoteRepositoryInterface::class);

        return $orderNoteRepository->getFromOrder($this);
    }

    /**
     * @return \MyParcelNL\Pdk\Validation\Validator\OrderValidator
     */
    public function getValidator(): OrderValidator
    {
        if (! $this->validator) {
            $this->validator = Pdk::get(OrderValidator::class);
            $this->validator->setOrder($this);
        }

        return $this->validator;
    }

    /**
     * @return bool
     */
    public function isDeliverable(): bool
    {
        return $this->lines->isDeliverable();
    }

    /**
     * Turns data into an array that should be stored in the plugin.
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStorableArray(): array
    {
        return [
            'apiIdentifier'   => $this->apiIdentifier,
            'exported'        => $this->exported,
            'deliveryOptions' => $this->deliveryOptions->toStorableArray(),
        ];
    }

    /**
     * @param  mixed $deliveryOptions
     *
     * @return self
     * @noinspection PhpUnused
     */
    protected function setDeliveryOptionsAttribute($deliveryOptions): self
    {
        $this->attributes['deliveryOptions'] = $deliveryOptions;
        $this->updateShipments();

        return $this;
    }

    /**
     * @param  mixed $orderLines
     *
     * @return self
     * @noinspection PhpUnused
     */
    protected function setLinesAttribute($orderLines): self
    {
        $this->attributes['lines'] = $orderLines;
        $this->updateTotals();

        return $this;
    }

    /**
     * @param  mixed $shipments
     *
     * @return self
     * @noinspection PhpUnused
     */
    protected function setShipmentsAttribute($shipments): self
    {
        $this->attributes['shipments'] = $shipments;
        $this->updateShipments();

        return $this;
    }

    /**
     * @return void
     */
    private function updateShipments(): void
    {
        $this->shipments->each(function (Shipment $shipment) {
            $shipment->orderId            = $this->externalIdentifier;
            $shipment->customsDeclaration = $shipment->customsDeclaration ?? $this->customsDeclaration;
            $shipment->deliveryOptions    = $shipment->deliveryOptions ?? $this->deliveryOptions;
            $shipment->recipient          = $shipment->recipient ?? $this->shippingAddress;
            $shipment->sender             = $shipment->sender ?? $this->senderAddress;
        });
    }

    /**
     * @return void
     */
    private function updateTotals(): void
    {
        [$price, $vat, $priceAfterVat] = $this->lines->reduce(
            function (array $carry, $line) {
                $quantity = Arr::get($line, 'quantity', 1);

                $carry[0] += $quantity * $line['price'];
                $carry[1] += $quantity * $line['vat'];
                $carry[2] += $quantity * $line['priceAfterVat'];

                return $carry;
            },
            [0, 0, 0]
        );

        $this->attributes['orderPrice']         = $price;
        $this->attributes['orderPriceAfterVat'] = $priceAfterVat;
        $this->attributes['orderVat']           = $vat;

        $this->attributes['totalPrice']         = $price + $this->attributes['shipmentPrice'];
        $this->attributes['totalPriceAfterVat'] = $priceAfterVat + $this->attributes['shipmentPriceAfterVat'];
        $this->attributes['totalVat']           = $vat + $this->attributes['shipmentVat'];
    }
}

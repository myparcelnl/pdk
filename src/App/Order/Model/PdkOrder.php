<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\App\Audit\Concern\HasAudits;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Base\Support\Utils;
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
 * @property null|string                                                 $referenceIdentifier
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration      $customsDeclaration
 * @property \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions              $deliveryOptions
 * @property \MyParcelNL\Pdk\App\Order\Collection\PdkOrderLineCollection $lines
 * @property \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection $notes
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $senderAddress
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $billingAddress
 * @property \MyParcelNL\Pdk\App\Order\Model\ShippingAddress             $shippingAddress
 * @property null|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
 * @property \MyParcelNL\Pdk\App\Order\Model\PdkPhysicalProperties       $physicalProperties
 * @property null|\DateTimeImmutable                                     $orderDate
 * @property bool                                                        $exported
 * @property null|bool                                                   $autoExported
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
class PdkOrder extends Model
{
    use HasAudits;

    protected $attributes      = [
        /** Plugin order id */
        'externalIdentifier'  => null,

        /** Fulfilment order ID from MyParcel */
        'apiIdentifier'       => null,

        /** Custom order number given by plugin */
        'referenceIdentifier' => null,

        'deliveryOptions' => DeliveryOptions::class,

        'senderAddress'      => null,
        'billingAddress'     => null,
        'shippingAddress'    => ShippingAddress::class,
        'autoExported'       => null,

        /**
         * Order shipments. Applicable when NOT using order mode.
         */
        'shipments'          => ShipmentCollection::class,

        /**
         * @deprecated Do not use, will be generated automatically. Will be removed in v3.0.0
         */
        'customsDeclaration' => null,

        'physicalProperties' => PdkPhysicalProperties::class,
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

    protected $auditIdentifier = 'externalIdentifier';

    protected $casts           = [
        'externalIdentifier' => 'string',
        'apiIdentifier'      => 'string',

        'deliveryOptions' => DeliveryOptions::class,

        'billingAddress'  => ContactDetails::class,
        'shippingAddress' => ShippingAddress::class,
        'senderAddress'   => ContactDetails::class,

        'shipments'          => ShipmentCollection::class,
        'customsDeclaration' => CustomsDeclaration::class,
        'physicalProperties' => PdkPhysicalProperties::class,
        'lines'              => PdkOrderLineCollection::class,
        'notes'              => PdkOrderNoteCollection::class,

        'orderDate'             => 'datetime',
        'referenceIdentifier'   => 'string',
        'exported'              => 'bool',
        'autoExported'          => 'bool',
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
     * @var null|\MyParcelNL\Pdk\Validation\Validator\OrderValidator
     */
    private $validator;

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        $this->updateTotals();
        $this->synchronizeShipments();
    }

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\Order $order
     *
     * @return self
     */
    public static function fromFulfilmentOrder(Order $order): self
    {
        return new self([
            'externalIdentifier'  => $order->externalIdentifier,
            'apiIdentifier'       => $order->uuid,
            'orderDate'           => $order->orderDate,
            'referenceIdentifier' => $order->referenceIdentifier,
            'invoiceAddress'      => $order->invoiceAddress,
            'dropOffPoint'        => $order->dropOffPoint,
            'notes'               => new PdkOrderNoteCollection($order->notes->all()),
            'lines'               => new PdkOrderLineCollection($order->lines->all()),
            'status'              => $order->status,
            'type'                => $order->type,
            'price'               => $order->price,
            'vat'                 => $order->vat,
            'priceAfterVat'       => $order->priceAfterVat,
            'createdAt'           => $order->createdAt,
            'updatedAt'           => $order->updatedAt,
        ]);
    }

    /**
     * @param  bool $store
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     * @throws \Exception
     */
    public function createShipment(bool $store = true): Shipment
    {
        $shipment = $this->synchronizeShipment(
            new Shipment([
                'carrier'            => $this->deliveryOptions->carrier,
                'customsDeclaration' => $this->customsDeclaration,
                'deliveryOptions'    => $this->deliveryOptions,
            ])
        );

        if ($store) {
            $this->shipments->push($shipment);
        }

        return $shipment;
    }

    /**
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     */
    public function getNotesAttribute(): PdkOrderNoteCollection
    {
        if (isset($this->attributes['notes'])) {
            return $this->getCastAttributeValue('notes');
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
     * @return array
     */
    public function toStorableArray(): array
    {
        return Utils::filterNull([
            'apiIdentifier'      => $this->apiIdentifier,
            'exported'           => $this->exported,
            'autoExported'       => $this->autoExported,
            'deliveryOptions'    => $this->deliveryOptions->toStorableArray(),
            'physicalProperties' => $this->physicalProperties->toStorableArray(),
        ]);
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
        $this->synchronizeShipments();

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
        $this->synchronizeShipments();

        return $this;
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     */
    protected function synchronizeShipment(Shipment $shipment): Shipment
    {
        $shipment->orderId             = $this->externalIdentifier;
        $shipment->referenceIdentifier = $this->externalIdentifier;
        $shipment->carrier             = $shipment->carrier ?? $this->deliveryOptions->carrier;
        $shipment->customsDeclaration  = $shipment->customsDeclaration ?? $this->customsDeclaration;
        $shipment->deliveryOptions     = $shipment->deliveryOptions ?? $this->deliveryOptions;
        $shipment->multiCollo          = $shipment->deliveryOptions->labelAmount > 1;
        $shipment->recipient           = $this->shippingAddress;
        $shipment->sender              = $this->senderAddress;
        $shipment->physicalProperties  = new PhysicalProperties([
            'height' => $this->physicalProperties->height,
            'length' => $this->physicalProperties->length,
            'width'  => $this->physicalProperties->width,
            'weight' => $this->physicalProperties->totalWeight,
        ]);

        return $shipment;
    }

    /**
     * @return $this
     */
    protected function synchronizeShipments(): self
    {
        $this->shipments->each([$this, 'synchronizeShipment']);

        return $this;
    }

    /**
     * @return void
     */
    private function updateTotals(): void
    {
        $this->physicalProperties->initialWeight = $this->lines
            ->onlyDeliverable()
            ->getTotalWeight();

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

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model;

use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Collection\PdkOrderLineCollection;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\DeliveryOptions;
use MyParcelNL\Pdk\Shipment\Model\Label;
use MyParcelNL\Pdk\Shipment\Model\PhysicalProperties;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Validation\Validator\OrderValidator;

/**
 * @property null|string                                                 $externalIdentifier
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration      $customsDeclaration
 * @property \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions              $deliveryOptions
 * @property null|\MyParcelNL\Pdk\Shipment\Model\Label                   $label
 * @property \MyParcelNL\Pdk\Plugin\Collection\PdkOrderLineCollection    $lines
 * @property \MyParcelNL\Pdk\Base\Model\ContactDetails                   $recipient
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $sender
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails              $billingAddress
 * @property null|\MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
 * @property null|\DateTimeImmutable                                     $orderDate
 * @property bool                                                        $exported
 * @property int                                                         $shipmentPrice
 * @property int                                                         $shipmentPriceAfterVat
 * @property int                                                         $shipmentVat
 * @property int                                                         $orderPrice
 * @property int                                                         $orderPriceAfterVat
 * @property int                                                         $orderVat
 * @property int                                                         $totalPrice
 * @property int                                                         $totalPriceAfterVat
 * @property int                                                         $totalVat
 * @method canHaveMultiCollo(): bool
 * @method canHaveSignature(): bool
 * @method canHaveInsurance(int $value = 100): bool
 * @method canHaveOnlyRecipient(): bool
 * @method canHaveAgeCheck(): bool
 * @method canHaveLargeFormat(): bool
 * @method canHaveWeight(int $value = 1): bool
 * @method canHaveDate(): bool
 */
class PdkOrder extends Model implements StorableArrayable
{
    protected $attributes = [
        /** Plugin order id */
        'externalIdentifier' => null,
        'deliveryOptions'    => DeliveryOptions::class,
        'label'              => null,

        'billingAddress'     => null,
        'recipient'          => ContactDetails::class,
        'sender'             => null,

        /**
         * Order shipments. Applicable when NOT using order mode.
         */
        'shipments'          => ShipmentCollection::class,
        'lines'              => PdkOrderLineCollection::class,
        'customsDeclaration' => null,
        'physicalProperties' => PhysicalProperties::class,

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
        'deliveryOptions'    => DeliveryOptions::class,
        'label'              => Label::class,

        'billingAddress' => ContactDetails::class,
        'recipient'      => ContactDetails::class,
        'sender'         => ContactDetails::class,

        'shipments'          => ShipmentCollection::class,
        'customsDeclaration' => CustomsDeclaration::class,
        'physicalProperties' => PhysicalProperties::class,
        'lines'              => PdkOrderLineCollection::class,

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
     * @param  array $data
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     * @throws \Exception
     */
    public function createShipment(array $data = []): Shipment
    {
        $deliveryOptions = $data[0]['deliveryOptions'] ?: $this->deliveryOptions;

        return new Shipment(
            array_replace_recursive(
                [
                    'customsDeclaration'  => $this->customsDeclaration,
                    'deliveryOptions'     => $deliveryOptions,
                    'recipient'           => $this->recipient,
                    'sender'              => $this->sender,
                    'referenceIdentifier' => $this->externalIdentifier,
                    'carrier'             => new CarrierOptions([
                        'carrier' => [
                            'name' => $deliveryOptions['carrier'],
                        ],
                    ]),
                    'orderId'             => $this->externalIdentifier,
                ],
                $data
            )
        );
    }

    /**
     * @return \MyParcelNL\Pdk\Validation\Validator\OrderValidator
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
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
     * Turns data into an array that should be stored in the plugin.
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStorableArray(): array
    {
        return [
            'exported'        => $this->exported,
            'deliveryOptions' => $this->deliveryOptions->toArrayWithoutNull(),
        ];
    }

    /**
     * @param  mixed $deliveryOptions
     *
     * @return $this
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
     * @return $this
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
     * @return \MyParcelNL\Pdk\Plugin\Model\PdkOrder
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
            $shipment->customsDeclaration = $this->customsDeclaration;
            $shipment->deliveryOptions    = $this->deliveryOptions;
            $shipment->recipient          = $this->recipient;
            $shipment->sender             = $this->sender;
        });
    }

    /**
     * @return void
     */
    private function updateTotals(): void
    {
        [$price, $vat, $priceAfterVat] = $this->lines->reduce(
            function (array $carry, $line) {
                $carry[0] += $line['quantity'] * $line['price'];
                $carry[1] += $line['quantity'] * $line['vat'];
                $carry[2] += $line['quantity'] * $line['priceAfterVat'];

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

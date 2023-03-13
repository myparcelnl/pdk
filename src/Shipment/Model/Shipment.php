<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use MyParcelNL\Pdk\Base\Contract\StorableArrayable;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Currency;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Base\Service\CountryCodes;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;
use MyParcelNL\Sdk\src\Support\Arr;

/**
 * @property null|int                                               $id
 * @property null|int                                               $shopId
 * @property null|string                                            $orderId
 * @property null|string                                            $referenceIdentifier
 * @property null|string                                            $externalIdentifier
 * @property null|string                                            $apiKey
 * @property null|string                                            $barcode
 * @property null|\MyParcelNL\Pdk\Carrier\Model\CarrierOptions      $carrier
 * @property null|string                                            $collectionContact
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration $customsDeclaration
 * @property bool                                                   $delayed
 * @property bool                                                   $delivered
 * @property \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions         $deliveryOptions
 * @property null|\MyParcelNL\Pdk\Shipment\Model\RetailLocation     $dropOffPoint
 * @property bool                                                   $hidden
 * @property bool                                                   $isReturn
 * @property null|string                                            $linkConsumerPortal
 * @property bool                                                   $multiCollo
 * @property null|string                                            $multiColloMainShipmentId
 * @property array                                                  $partnerTrackTraces
 * @property null|\MyParcelNL\Pdk\Shipment\Model\PhysicalProperties $physicalProperties
 * @property \MyParcelNL\Pdk\Base\Model\Currency                    $price
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails         $recipient
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails         $sender
 * @property null|int                                               $shipmentType
 * @property null|int                                               $status
 * @property null|\DateTime                                         $deleted
 * @property null|\DateTime                                         $updated
 * @property null|\DateTime                                         $created
 * @property null|int                                               $createdBy
 * @property null|\DateTime                                         $modified
 * @property null|int                                               $modifiedBy
 */
class Shipment extends Model implements StorableArrayable
{
    public const SHIPMENT_TYPE_STANDARD       = 1;
    public const SHIPMENT_TYPE_RETURN         = 2;
    public const SHIPMENT_TYPE_MULTI_COLLO    = 3;
    public const SHIPMENT_TYPE_LABEL_PACKAGE  = 4;
    public const SHIPMENT_TYPE_RETURN_ERS     = 5;
    public const SHIPMENT_TYPE_RETURN_SPECIAL = 6;
    public const SHIPMENT_TYPE_EXPRESS        = 7;
    public const RETURN_SHIPMENT_TYPES        = [
        self::SHIPMENT_TYPE_RETURN,
        self::SHIPMENT_TYPE_RETURN_ERS,
        self::SHIPMENT_TYPE_RETURN_SPECIAL,
    ];

    protected $attributes = [
        /**
         * ID of the order this shipment belongs to, if any.
         */
        'orderId'                  => null,

        /**
         * MultiCollo shipments are shipments that are split into multiple shipments.
         */
        'multiCollo'               => false,

        /**
         * The date and time when the shipment was last updated. Supposed to be saved in the plugin.
         */
        'updated'                  => null,

        /**
         * PLUGIN ONLY: Whether the shipment is deleted.
         */
        'deleted'                  => null,

        /**
         * Shipment ID. Filled by the API after exporting shipment.
         */
        'id'                       => null,
        'shopId'                   => null,
        'referenceIdentifier'      => null,
        'externalIdentifier'       => null,
        'apiKey'                   => null,
        'barcode'                  => null,
        'carrier'                  => null,
        'collectionContact'        => null,
        'customsDeclaration'       => null,
        'delayed'                  => false,
        'delivered'                => false,
        'deliveryOptions'          => DeliveryOptions::class,
        'dropOffPoint'             => null,
        'hidden'                   => false,
        'isReturn'                 => false,
        /**
         * The link to the track and trace page of the consumer portal.
         */
        'linkConsumerPortal'       => null,
        /**
         * Main shipment if this shipment belongs to a multi-collo shipment.
         */
        'multiColloMainShipmentId' => null,
        /**
         * Partner track traces are track traces of other carriers that are linked to this shipment.
         */
        'partnerTrackTraces'       => [],
        /**
         * Physical properties of the shipment.
         */

        'physicalProperties' => null,
        /**
         * The billed price of the shipment.
         */
        'price'              => Currency::class,
        /**
         * The recipient of the shipment.
         */
        'recipient'          => ContactDetails::class,
        /**
         * The sender of the shipment.
         */
        'sender'             => ContactDetails::class,
        /**
         * Type of shipment.
         */
        'shipmentType'       => null,
        /**
         * Status of the shipment.
         *
         * @see https://developer.myparcel.nl/api-reference/04.data-types.html#shipment-status
         */
        'status'             => null,
        /**
         * The date and time when the shipment was created in the API.
         */
        'created'            => null,
        /**
         * The id of the user that created the shipment in the API.
         */
        'createdBy'          => null,
        /**
         * The date and time when the shipment was last updated in the API.
         */
        'modified'           => null,
        /**
         * The id of the user that last updated the shipment in the API.
         */
        'modifiedBy'         => null,
    ];

    protected $casts      = [
        'id'                       => 'int',
        'shopId'                   => 'int',
        'orderId'                  => 'string',
        'referenceIdentifier'      => 'string',
        'externalIdentifier'       => 'string',
        'apiKey'                   => 'string',
        'barcode'                  => 'string',
        'carrier'                  => CarrierOptions::class,
        'collectionContact'        => 'string',
        'customsDeclaration'       => CustomsDeclaration::class,
        'delayed'                  => 'bool',
        'delivered'                => 'bool',
        'deliveryOptions'          => DeliveryOptions::class,
        'dropOffPoint'             => RetailLocation::class,
        'hidden'                   => 'bool',
        'isReturn'                 => 'bool',
        'linkConsumerPortal'       => 'string',
        'multiCollo'               => 'bool',
        'multiColloMainShipmentId' => 'string',
        'partnerTrackTraces'       => 'array',
        'physicalProperties'       => PhysicalProperties::class,
        'price'                    => Currency::class,
        'recipient'                => ContactDetails::class,
        'sender'                   => ContactDetails::class,
        'shipmentType'             => 'int',
        'status'                   => 'int',
        'deleted'                  => DateTime::class,
        'updated'                  => DateTime::class,
        'created'                  => DateTime::class,
        'createdBy'                => 'int',
        'modified'                 => DateTime::class,
        'modifiedBy'               => 'int',
    ];

    /**
     * Carrier is passed to the delivery options.
     *
     * @param  null|array $data
     */
    public function __construct(?array $data = [])
    {
        parent::__construct($data);
        $this->updateCarrier();
    }

    /**
     * @return string
     */
    public function getTrackTraceLink(): string
    {
        if (CountryCodes::CC_NL === $this->recipient->cc) {
            return sprintf(
                'https://myparcel.me/track-trace/%s/%s/%s',
                $this->barcode,
                $this->recipient->postalCode,
                $this->recipient->cc
            );
        }

        return sprintf(
            'https://www.internationalparceltracking.com/Main.aspx#/track/%s/%s/%s',
            $this->barcode,
            $this->recipient->cc,
            $this->recipient->postalCode
        );
    }

    /**
     * Returns the model as an array that can be saved in a database.
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function toStorableArray(): array
    {
        $this->updated = new DateTime();

        $attributes = $this->toArrayWithoutNull();

        // TODO: replace this with except() when nested properties are supported
        Arr::forget($attributes, ['carrier.capabilities', 'carrier.returnCapabilities']);

        return $attributes;
    }

    /**
     * @param  int|string|\MyParcelNL\Pdk\Carrier\Model\CarrierOptions $carrier
     *
     * @return $this
     * @throws \Exception
     */
    protected function setCarrierAttribute($carrier): self
    {
        $this->attributes['carrier'] = $carrier;
        $this->updateCarrier();

        return $this;
    }

    /**
     * @param  array|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     *
     * @return $this
     * @throws \Exception
     */
    protected function setDeliveryOptionsAttribute($deliveryOptions): self
    {
        $this->attributes['deliveryOptions'] = $deliveryOptions;
        $this->updateCarrier();

        return $this;
    }

    /**
     * @return void
     * @throws \Exception
     */
    private function updateCarrier(): void
    {
        // In case the model hasn't fully initialized yet (e.g. in the constructor).
        if (is_string($this->attributes['deliveryOptions']) || is_string($this->attributes['carrier'])) {
            return;
        }

        if ($this->carrier) {
            $this->attributes['deliveryOptions']['carrier'] = $this->carrier->carrier->name;
        } elseif ($this->deliveryOptions->carrier) {
            $this->attributes['carrier'] = new CarrierOptions([
                'carrier' => [
                    'name' => $this->deliveryOptions->carrier,
                ],
            ]);
        }
    }
}

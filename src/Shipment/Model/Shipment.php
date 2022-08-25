<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;

/**
 * @property null|int                                               $id
 * @property null|string                                            $referenceIdentifier
 * @property null|string                                            $externalIdentifier
 * @property null|string                                            $apiKey
 * @property null|string                                            $barcode
 * @property null|\MyParcelNL\Pdk\Carrier\Model\CarrierOptions      $carrier
 * @property null|string                                            $collectionContact
 * @property null|\DateTime                                         $created
 * @property null|string                                            $createdBy
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration $customsDeclaration
 * @property null|bool                                              $delayed
 * @property null|bool                                              $delivered
 * @property \MyParcelNL\Pdk\Shipment\Model\DeliveryOptions         $deliveryOptions
 * @property null|\MyParcelNL\Pdk\Shipment\Model\RetailLocation     $dropOffPoint
 * @property null|bool                                              $isReturn
 * @property null|string                                            $linkConsumerPortal
 * @property null|\DateTime                                         $modified
 * @property null|string                                            $modifiedBy
 * @property bool                                                   $multiCollo
 * @property null|string                                            $multiColloMainShipmentId
 * @property null|array                                             $partnerTrackTraces
 * @property null|\MyParcelNL\Pdk\Shipment\Model\PhysicalProperties $physicalProperties
 * @property \MyParcelNL\Pdk\Base\Model\ContactDetails              $recipient
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails         $sender
 * @property null|int                                               $shopId
 * @property null|string                                            $orderId
 * @property null|int                                               $status
 * @property null|bool                                              $updated
 */
class Shipment extends Model
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
        'id'                       => null,
        'referenceIdentifier'      => null,
        'externalIdentifier'       => null,
        'apiKey'                   => null,
        'barcode'                  => null,
        'carrier'                  => null,
        'collectionContact'        => null,
        'created'                  => null,
        'createdBy'                => null,
        'customsDeclaration'       => null,
        'delayed'                  => false,
        'delivered'                => false,
        'deliveryOptions'          => DeliveryOptions::class,
        'dropOffPoint'             => null,
        'isReturn'                 => false,
        'linkConsumerPortal'       => null,
        'modified'                 => null,
        'modifiedBy'               => null,
        'multiCollo'               => false,
        'multiColloMainShipmentId' => null,
        'partnerTrackTraces'       => null,
        'physicalProperties'       => null,
        'recipient'                => ContactDetails::class,
        'sender'                   => null,
        'shopId'                   => null,
        'orderId'                  => null,
        'status'                   => null,
        'updated'                  => null,
    ];

    protected $casts      = [
        'id'                       => 'int',
        'referenceIdentifier'      => 'string',
        'externalIdentifier'       => 'string',
        'apiKey'                   => 'string',
        'barcode'                  => 'string',
        'carrier'                  => CarrierOptions::class,
        'collectionContact'        => 'string',
        'created'                  => DateTime::class,
        'createdBy'                => 'int',
        'customsDeclaration'       => CustomsDeclaration::class,
        'delayed'                  => 'bool',
        'delivered'                => 'bool',
        'deliveryOptions'          => DeliveryOptions::class,
        'dropOffPoint'             => RetailLocation::class,
        'isReturn'                 => 'bool',
        'linkConsumerPortal'       => 'string',
        'modified'                 => DateTime::class,
        'modifiedBy'               => 'int',
        'multiCollo'               => 'bool',
        'multiColloMainShipmentId' => 'string',
        'partnerTrackTraces'       => 'array',
        'physicalProperties'       => PhysicalProperties::class,
        'recipient'                => ContactDetails::class,
        'sender'                   => ContactDetails::class,
        'shopId'                   => 'int',
        'orderId'                  => 'string',
        'status'                   => 'int',
        'updated'                  => 'bool',
    ];

    /**
     * Carrier is passed to the delivery options.
     *
     * @param  null|array $data
     */
    public function __construct(?array $data = [])
    {
        parent::__construct($data);
        $this->setDeliveryOptionsCarrier();
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
        $this->setDeliveryOptionsCarrier();

        return $this;
    }

    /**
     * @param  array|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions $deliveryOptions
     *
     * @return $this
     */
    protected function setDeliveryOptionsAttribute($deliveryOptions): self
    {
        $this->attributes['deliveryOptions'] = $deliveryOptions;
        $this->setDeliveryOptionsCarrier();

        return $this;
    }

    /**
     * @return void
     */
    private function setDeliveryOptionsCarrier(): void
    {
        // In case the model hasn't fully initialized yet (e.g. in the constructor).
        if (! $this->attributes['deliveryOptions']
            || is_string($this->attributes['deliveryOptions'])
            || ! $this->attributes['carrier']
            || is_string($this->attributes['carrier'])) {
            return;
        }

        $this->deliveryOptions->carrier = $this->carrier->name;
    }
}

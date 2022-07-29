<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Model;

use DateTime;
use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Carrier\Model\CarrierOptions;

/**
 * @property null|string                                            $apiKey
 * @property null|string                                            $barcode
 * @property null|\MyParcelNL\Pdk\Carrier\Model\CarrierOptions      $carrier
 * @property null|string                                            $collectionContact
 * @property null|datetime                                          $created
 * @property null|string                                            $createdBy
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration $customsDeclaration
 * @property null|bool                                              $delayed
 * @property null|bool                                              $delivered
 * @property null|\MyParcelNL\Pdk\Shipment\Model\DeliveryOptions    $deliveryOptions
 * @property null|\MyParcelNL\Pdk\Shipment\Model\RetailLocation     $dropOffPoint
 * @property null|string                                            $externalIdentifier
 * @property null|int                                               $id
 * @property null|bool                                              $isReturn
 * @property null|string                                            $linkConsumerPortal
 * @property null|\DateTime                                         $modified
 * @property null|string                                            $modifiedBy
 * @property null|bool                                              $multiCollo
 * @property null|string                                            $multiColloMainShipmentId
 * @property null|array                                             $partnerTrackTraces
 * @property null|\MyParcelNL\Pdk\Shipment\Model\PhysicalProperties $physicalProperties
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails         $recipient
 * @property null|string                                            $referenceIdentifier
 * @property null|\MyParcelNL\Pdk\Base\Model\ContactDetails         $sender
 * @property null|int                                               $shopId
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
        'externalIdentifier'       => null,
        'id'                       => null,
        'isReturn'                 => false,
        'linkConsumerPortal'       => null,
        'modified'                 => null,
        'modifiedBy'               => null,
        'multiCollo'               => false,
        'multiColloMainShipmentId' => null,
        'partnerTrackTraces'       => null,
        'physicalProperties'       => null,
        'recipient'                => null,
        'referenceIdentifier'      => null,
        'sender'                   => null,
        'shopId'                   => null,
        'status'                   => null,
        'updated'                  => null,
    ];

    protected $casts      = [
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
        'externalIdentifier'       => 'string',
        'id'                       => 'int',
        'isReturn'                 => 'bool',
        'linkConsumerPortal'       => 'string',
        'modified'                 => DateTime::class,
        'modifiedBy'               => 'int',
        'multiCollo'               => 'bool',
        'multiColloMainShipmentId' => 'string',
        'partnerTrackTraces'       => 'array',
        'physicalProperties'       => PhysicalProperties::class,
        'recipient'                => ContactDetails::class,
        'referenceIdentifier'      => 'string',
        'sender'                   => ContactDetails::class,
        'shopId'                   => 'int',
        'status'                   => 'int',
        'updated'                  => 'bool',
    ];

    /**
     * Carrier is passed to the delivery options.
     *
     * @param  array $data
     *
     * @throws \Exception
     */
    public function __construct(array $data = [])
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

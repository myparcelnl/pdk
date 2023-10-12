<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\PhysicalProperties;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\Shipment as PdkShipment;

/**
 * @property int                                                    $carrier
 * @property string                                                 $contractId
 * @property null|\MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration $customsDeclaration
 * @property \MyParcelNL\Pdk\Fulfilment\Model\ShipmentOptions       $options
 * @property null|\MyParcelNL\Pdk\Shipment\Model\RetailLocation     $pickup
 * @property \MyParcelNL\Pdk\Base\Model\ContactDetails              $recipient
 * @property null|\MyParcelNL\Pdk\Shipment\Model\RetailLocation     $dropOffPoint
 * @property \MyParcelNL\Pdk\Shipment\Model\PhysicalProperties      $physicalProperties
 */
class Shipment extends Model
{
    public $attributes = [
        'carrier'            => null,
        'contractId'         => null,
        'customsDeclaration' => null,
        'options'            => ShipmentOptions::class,
        'pickup'             => null,
        'recipient'          => ContactDetails::class,
        'physicalProperties' => PhysicalProperties::class,
        'dropOffPoint'       => null,
    ];

    public $casts      = [
        'carrier'            => 'int',
        'contractId'         => 'string',
        'customsDeclaration' => CustomsDeclaration::class,
        'options'            => ShipmentOptions::class,
        'pickup'             => RetailLocation::class,
        'recipient'          => ContactDetails::class,
        'physicalProperties' => PhysicalProperties::class,
        'dropOffPoint'       => RetailLocation::class,
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);
        $this->attributes['carrier'] = $this->attributes['carrier'] ?? Platform::get('defaultCarrierId');
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Shipment\Model\Shipment $pdkShipment
     *
     * @return \MyParcelNL\Pdk\Fulfilment\Model\Shipment
     */
    public static function fromPdkShipment(?PdkShipment $pdkShipment): self
    {
        if (! $pdkShipment) {
            return new self();
        }

        return new self([
            'carrier'            => $pdkShipment->carrier->id,
            'contractId'         => $pdkShipment->carrier->subscriptionId,
            'customsDeclaration' => $pdkShipment->customsDeclaration,
            'options'            => ShipmentOptions::fromPdkDeliveryOptions($pdkShipment->deliveryOptions),
            'pickup'             => $pdkShipment->deliveryOptions->pickupLocation,
            'recipient'          => $pdkShipment->recipient,
            'dropOffPoint'       => $pdkShipment->dropOffPoint,
            'physicalProperties' => [
                'height'        => $pdkShipment->physicalProperties->height ?? 0,
                'width'         => $pdkShipment->physicalProperties->width ?? 0,
                'length'        => $pdkShipment->physicalProperties->length ?? 0,
                'initialWeight' => $pdkShipment->physicalProperties->initialWeight,
                'manualWeight'  => $pdkShipment->physicalProperties->manualWeight,
            ],
        ]);
    }
}

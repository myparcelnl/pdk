<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\Base\Model\ContactDetails;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Shipment\Model\CustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Model\PhysicalProperties;
use MyParcelNL\Pdk\Shipment\Model\RetailLocation;
use MyParcelNL\Pdk\Shipment\Model\Shipment as PdkShipment;

/**
 * @property string                                                 $orderId
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
        'orderId'            => null,
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
        'orderId'            => 'string',
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
        $propositionService = Pdk::get(PropositionService::class);
        $this->attributes['carrier'] = $this->attributes['carrier'] ?? $propositionService->getDefaultCarrier()->id;
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
            'orderId'            => $pdkShipment->orderId,
            'carrier'            => $pdkShipment->carrier->id,
            'contractId'         => $pdkShipment->carrier->contractId,
            'customsDeclaration' => $pdkShipment->customsDeclaration,
            'options'            => ShipmentOptions::fromPdkDeliveryOptions($pdkShipment->deliveryOptions),
            'pickup'             => $pdkShipment->deliveryOptions->pickupLocation ?? null,
            'recipient'          => $pdkShipment->recipient,
            'dropOffPoint'       => $pdkShipment->dropOffPoint,
            'physicalProperties' => $pdkShipment->physicalProperties,
        ]);
    }
}

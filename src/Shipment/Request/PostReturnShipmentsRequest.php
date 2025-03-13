<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Types\Service\TriStateService;
use MyParcelNL\Pdk\Base\Model\ContactDetails;

class PostReturnShipmentsRequest extends Request
{
    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipmentCollection
     * @param  array                                                  $parameters
     */
    public function __construct(ShipmentCollection $shipmentCollection, array $parameters = [])
    {
        $this->collection = $shipmentCollection;
        parent::__construct(['parameters' => $parameters]);
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        $data = [
            'data' => [
                'return_shipments' => $this->encodeReturnShipments(),
            ],
        ];

        return json_encode($data);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/vnd.return_shipment+json;charset=utf-8',
        ];
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return 'POST';
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return '/shipments';
    }

    /**
     * @return array
     */
    private function encodeReturnShipments(): array
    {
        $returnShipments = [];
        
        foreach ($this->collection as $shipment) {
            $shipment = $this->ensureReturnCapabilities($shipment);

            // Get the original recipient data
            $recipient = $shipment->recipient;
            if (!$recipient) {
                throw new \RuntimeException('Recipient data is required for return shipments');
            }

            // Create a new array with only the required fields
            $returnShipment = [
                'parent' => $shipment->id,
                'reference_identifier' => $shipment->referenceIdentifier,
                'carrier' => $shipment->carrier->id,
                'email' => $recipient->email,
                'name' => $recipient->person,
                'options' => [
                    'package_type' => $shipment->deliveryOptions->getPackageTypeId()
                ],
                'sender' => [
                    'cc' => $recipient->cc,
                    'city' => $recipient->city,
                    'person' => $recipient->person,
                    'postal_code' => $recipient->postalCode,
                    'street' => $recipient->address1,
                    'number' => '',
                    'region' => $recipient->region,
                    'company' => $recipient->company,
                    'phone' => $recipient->phone
                ]
            ];

            // Remove any null values from the main array and sender array
            $returnShipment = array_filter($returnShipment, function($value) {
                return $value !== null;
            });

            // Remove any null values from the sender array
            $returnShipment['sender'] = array_filter($returnShipment['sender'], function($value) {
                return $value !== null;
            });

            $returnShipments[] = $returnShipment;
        }

        return $returnShipments;
    }

    /**
     * If the carrier cannot handle return shipments, the carrier will be set to the platform default carrier.
     * In that case a notification is emitted.
     *
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     */
    private function ensureReturnCapabilities(Shipment $shipment): Shipment
    {
        $carrierId = $shipment->carrier->id;
        $carrier   = Platform::getCarriers()
            ->firstWhere('id', $carrierId);

        if (! $carrier || ! $carrier->returnCapabilities) {
            Notifications::warning(
                "{$shipment->carrier->human} has no return capabilities",
                'Return shipment exported with default carrier ' . Platform::get('defaultCarrier'),
                Notification::CATEGORY_ACTION,
                [
                    'action'   => PdkBackendActions::EXPORT_RETURN,
                    'orderIds' => $shipment->referenceIdentifier,
                ]
            );
            $shipment->carrier = new Carrier(['carrierId' => Platform::get('defaultCarrierId')]);
        }

        return $shipment;
    }
}

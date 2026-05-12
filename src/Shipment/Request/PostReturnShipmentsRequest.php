<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Carrier\Model\Carrier;
use MyParcelNL\Pdk\Carrier\Service\CapabilitiesValidationService;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Concern\EncodesRecipient;

class PostReturnShipmentsRequest extends Request
{
    use EncodesRecipient;

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
        return json_encode([
            'data' => [
                'return_shipments' => $this->encodeReturnShipments(),
            ],
        ]);
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
            $recipient = $shipment->recipient;
            if (! $recipient) {
                throw new \RuntimeException('Recipient data is required for return shipments');
            }

            $shipment = $this->ensureReturnCapabilities($shipment, $recipient->cc);

            $carrierId = Utils::convertToId($shipment->carrier->carrier, Carrier::CARRIER_NAME_ID_MAP);
            if (! $carrierId) {
                throw new \InvalidArgumentException(sprintf('Cannot encode return shipment: carrier %s is not mapped to an ID.', $shipment->carrier->carrier));
            }

            // Create a new array with only the required fields
            $returnShipment = [
                'parent' => $shipment->id,
                'reference_identifier' => $shipment->referenceIdentifier,
                'carrier' => $carrierId,
                'email' => $recipient->email,
                'name' => $recipient->person,
                'options' => [
                    'package_type' => $shipment->deliveryOptions->getPackageTypeId()
                ]
            ];

            // Add sender details from recipient data
            $returnShipment['sender'] = $this->encodeRecipient($recipient);


            $returnShipments[] = Utils::filterNull($returnShipment);
        }

        return $returnShipments;
    }

    /**
     * If the carrier cannot handle return shipments to the destination, swap to the
     * platform default carrier and emit a notification.
     *
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     * @param  string                                  $destinationCc ISO 3166-1 alpha-2 destination country code
     *
     * @return \MyParcelNL\Pdk\Shipment\Model\Shipment
     */
    private function ensureReturnCapabilities(Shipment $shipment, string $destinationCc): Shipment
    {
        $capabilitiesValidationService = Pdk::get(CapabilitiesValidationService::class);

        if (! $capabilitiesValidationService->supportsReturns($shipment->carrier, $destinationCc)) {
            $carrierName        = $shipment->carrier->carrier;
            $propositionService = Pdk::get(PropositionService::class);
            $defaultCarrier     = $propositionService->getDefaultCarrier();
            Notifications::warning(
                "{$carrierName} has no return capabilities",
                'Return shipment exported with default carrier ' . $defaultCarrier->carrier,
                Notification::CATEGORY_ACTION,
                [
                    'action'   => PdkBackendActions::EXPORT_RETURN,
                    'orderIds' => $shipment->referenceIdentifier,
                ]
            );
            $shipment->carrier = $defaultCarrier;
        }

        return $shipment;
    }
}

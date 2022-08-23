<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Base\Request\AbstractRequest;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Shipment\Concern\HasEncodesShipment;
use MyParcelNL\Pdk\Shipment\Model\Shipment;

class PostOrdersRequest extends AbstractRequest
{
    use HasEncodesShipment;

    /**
     * @var string
     */
    protected $path = '/fulfilment/orders';

    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection $collection
     */
    public function __construct(OrderCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return null|string
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'orders' => $this->collection->map(function ($order) {
                    return $this->encodeOrder($order);
                }),
            ],
        ]);
    }

    /**
     * @return string[]
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/vnd.shipment+json;charset=utf-8;version=1.1',
        ];
    }

    /**
     * @return string
     */
    public function getHttpMethod(): string
    {
        return 'POST';
    }

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\Order $order
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeOrder(Order $order): array
    {
        return [
            'external_identifier'           => $order->externalIdentifier,
            'fulfilment_partner_identifier' => $order->fulfilmentPartnerIdentifier,
            'order_date'                    => $order->orderDate,
            'invoice_address'               => $order->invoiceAddress,
            'order_lines'                   => $order->orderLines->toArrayWithoutNull(),
            'shipment'                      => $this->encodeShipment($order->shipment),
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Model\Shipment $shipment
     *
     * @return array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeShipment(Shipment $shipment): array
    {
        return [
            'carrier'             => $shipment->carrier->id,
            'customs_declaration' => $shipment->customsDeclaration
                ? array_filter($shipment->customsDeclaration->toSnakeCaseArray())
                : null,
            'drop_off_point'      => $this->encodeDropOffPoint($shipment),
            'options'             => $this->encodeOptions($shipment),
            'physical_properties' => $shipment->physicalProperties
                ? [
                    'weight' => $this->getWeight($shipment)
                ]
                : null,
            'pickup'              => $shipment->deliveryOptions->pickupLocation
                ? ['location_code' => $shipment->deliveryOptions->pickupLocation->locationCode]
                : null,
            'recipient'           => $shipment->recipient->toSnakeCaseArray(),
        ];
    }
}

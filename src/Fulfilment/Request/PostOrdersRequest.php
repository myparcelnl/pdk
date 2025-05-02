<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Contract\Arrayable;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Model\OrderLine;
use MyParcelNL\Pdk\Fulfilment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Concern\EncodesCustomsDeclaration;
use MyParcelNL\Pdk\Shipment\Concern\EncodesRecipient;

class PostOrdersRequest extends Request
{
    use EncodesCustomsDeclaration;
    use EncodesRecipient;

    /**
     * @var \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection $collection
     */
    public function __construct(OrderCollection $collection)
    {
        parent::__construct();
        $this->collection = $collection;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return json_encode([
            'data' => [
                'orders' => array_map(function (Order $order) {
                    return $this->encodeOrder($order);
                }, $this->collection->all()),
            ],
        ]);
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
        return '/fulfilment/orders';
    }

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\Order $order
     *
     * @return array
     */
    private function encodeOrder(Order $order): array
    {
        return [
            'external_identifier'           => $order->externalIdentifier,
            'fulfilment_partner_identifier' => $order->fulfilmentPartnerIdentifier,
            'invoice_address'               => $this->encodeRecipient($order->invoiceAddress),
            'order_date'                    => $order->orderDate
                ? $order->orderDate->format(Pdk::get('defaultDateFormat'))
                : null,
            'order_lines'                   => $order->lines->reduce(
                function (array $carry, OrderLine $orderLine) {
                    $carry[] = $orderLine->except('vat', Arrayable::ENCODED);

                    return $carry;
                },
                []
            ),
            'shipment'                      => $this->getShipment($order),
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\Order $order
     *
     * @return array
     */
    private function getShipment(Order $order): array
    {
        $shipment = $order->shipment;

        return [
            'carrier'             => $shipment->carrier,
            'contract_id'         => $shipment->contractId ? (int) $shipment->contractId : null,
            'customs_declaration' => $this->encodeCustomsDeclaration($shipment),
            'drop_off_point'      => $shipment->dropOffPoint
                ? $shipment->dropOffPoint->toArray(Arrayable::ENCODED)
                : null,
            'options'             => $this->getShipmentOptions($shipment),
            'physical_properties' => $shipment->physicalProperties->toArray(Arrayable::ENCODED),
            'pickup'              => $shipment->pickup
                ? $shipment->pickup->toArray(Arrayable::ENCODED)
                : null,
            'recipient'           => $this->encodeRecipient($shipment->recipient),
        ];
    }

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Model\Shipment $shipment
     *
     * @return array
     */
    private function getShipmentOptions(Shipment $shipment): array
    {
        $options = $shipment->options->toArray(Arrayable::ENCODED);

        $options['insurance'] = [
            'amount'   => $shipment->options->insurance,
            'currency' => 'EUR',
        ];

        return array_map(static function ($item) {
            return is_bool($item) ? (int) $item : $item;
        }, $options);
    }
}

<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Request;

use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Base\Model\Address;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderCollection;
use MyParcelNL\Pdk\Fulfilment\Model\Order;
use MyParcelNL\Pdk\Fulfilment\Model\OrderLine;

class PostOrdersRequest extends Request
{
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
     * @return null|string
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
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
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function encodeOrder(Order $order): array
    {
        $orderLines = $order->orderLines->reduce(function (array $carry, OrderLine $orderLine) {
            $orderLineArray = $orderLine->toSnakeCaseArray();
            unset($orderLineArray['vat']);
            $carry[] = $orderLineArray;
            return $carry;
        }, []);

        return [
            'external_identifier'           => $order->externalIdentifier,
            'fulfilment_partner_identifier' => $order->fulfilmentPartnerIdentifier,
            'invoice_address'               => $this->getAddress($order->invoiceAddress),
            'order_date'                    => $order->orderDate ? $order->orderDate->format('Y-m-d H:i:s') : null,
            'order_lines'                   => $orderLines,
            'shipment'                      => $this->getShipment($order),
        ];
    }

    /**
     * @param  null|\MyParcelNL\Pdk\Base\Model\Address $address
     *
     * @return null|array
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getAddress(?Address $address): ?array
    {
        if (! $address) {
            return null;
        }

        $addressSnakeCase = $address->toSnakeCaseArray();

        return array_reduce(
            array_keys($addressSnakeCase),
            static function (array $carry, $key) use ($addressSnakeCase) {
                if (null !== $addressSnakeCase[$key] && ! in_array($key, ['full_street', 'street_additional_info'])) {
                    $carry[$key] = (string) $addressSnakeCase[$key];
                }

                return $carry;
            },
            []
        );
    }

    /**
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    private function getShipment(Order $order): ?array
    {
        $shipment                     = $order->shipment;
        $shipment->recipient          = $this->getAddress($shipment->recipient);
        $shipment->pickup             = $this->getAddress($shipment->pickup);
        $shipment->options->insurance = ['amount' => $shipment->options->insurance, 'currency' => 'EUR'];
        $shipment->options            = array_map(static function ($item) {
            return is_bool($item) ? (int) $item : $item;
        }, $shipment->options->toSnakeCaseArray());

        return $shipment->toSnakeCaseArray() ?: null;
    }
}
